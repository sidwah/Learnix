<?php include '../includes/signin-header.php'; ?>
<?php
// require_once 'db_connection.php'; // adjust as needed

$instructor = null;
$reviews = [];
$total_reviews = 0;
$average_rating = 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Reviews per page
$offset = ($page - 1) * $limit;
$rating_filter = isset($_GET['rating']) && in_array($_GET['rating'], [1, 2, 3, 4, 5]) ? (int)$_GET['rating'] : null;
$sort = isset($_GET['sort']) && in_array($_GET['sort'], ['newest', 'oldest', 'highest', 'lowest']) ? $_GET['sort'] : 'newest';

if (isset($_GET['username'])) {
    $username = $_GET['username'];

    try {
        // Get instructor info
        $stmt = $conn->prepare("
            SELECT 
                u.user_id,
                u.username,
                u.first_name, 
                u.last_name, 
                u.profile_pic,
                i.instructor_id,
                i.verification_status,
                
                -- Average Rating across all courses
                (
                    SELECT COALESCE(AVG(cr.rating), 0)
                    FROM course_ratings cr
                    JOIN courses c ON cr.course_id = c.course_id
                    WHERE c.instructor_id = i.instructor_id
                ) AS average_rating,
                
                -- Review Count
                (
                    SELECT COUNT(*)
                    FROM course_ratings cr
                    JOIN courses c ON cr.course_id = c.course_id
                    WHERE c.instructor_id = i.instructor_id
                ) AS review_count
            FROM instructors i
            JOIN users u ON i.user_id = u.user_id
            WHERE u.username = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $instructor = $result->fetch_assoc();

        if (!$instructor) {
            header("HTTP/1.0 404 Not Found");
            echo "Instructor not found.";
            exit;
        }

        // Get rating distribution
        $stmt = $conn->prepare("
            SELECT 
                rating, 
                COUNT(*) as count
            FROM course_ratings cr
            JOIN courses c ON cr.course_id = c.course_id
            WHERE c.instructor_id = ?
            GROUP BY rating
            ORDER BY rating DESC
        ");
        $stmt->bind_param("i", $instructor['instructor_id']);
        $stmt->execute();
        $rating_counts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $ratings_distribution = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0
        ];
        
        $total_reviews = 0;
        foreach ($rating_counts as $rc) {
            $ratings_distribution[$rc['rating']] = $rc['count'];
            $total_reviews += $rc['count'];
        }
        
        // Calculate percentages for progress bars
        $ratings_percentages = [];
        foreach ($ratings_distribution as $rating => $count) {
            $ratings_percentages[$rating] = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
        }

        // Build the query for reviews
        $query = "
            SELECT cr.rating_id, cr.review_text, cr.rating, cr.created_at,
                  u.user_id, u.first_name, u.last_name, u.profile_pic,
                  c.title as course_title, c.course_id, c.thumbnail
            FROM course_ratings cr
            JOIN courses c ON cr.course_id = c.course_id
            JOIN users u ON cr.user_id = u.user_id
            WHERE c.instructor_id = ? ";
        
        $query_params = [$instructor['instructor_id']];
        
        // Add rating filter if selected
        if ($rating_filter !== null) {
            $query .= "AND cr.rating = ? ";
            $query_params[] = $rating_filter;
        }
        
        // Add review text filter (only show reviews with text)
        $query .= "AND cr.review_text IS NOT NULL AND cr.review_text != '' ";
        
        // Add sorting
        switch ($sort) {
            case 'oldest':
                $query .= "ORDER BY cr.created_at ASC ";
                break;
            case 'highest':
                $query .= "ORDER BY cr.rating DESC, cr.created_at DESC ";
                break;
            case 'lowest':
                $query .= "ORDER BY cr.rating ASC, cr.created_at DESC ";
                break;
            case 'newest':
            default:
                $query .= "ORDER BY cr.created_at DESC ";
                break;
        }
        
        // Add pagination
        $query .= "LIMIT ?, ?";
        $query_params[] = $offset;
        $query_params[] = $limit;
        
        // Prepare for different parameter types
        $stmt = $conn->prepare($query);
        $types = str_repeat('i', count($query_params));
        $stmt->bind_param($types, ...$query_params);
        $stmt->execute();
        $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get total reviews for pagination (considering filters)
        $count_query = "
            SELECT COUNT(*) as total
            FROM course_ratings cr
            JOIN courses c ON cr.course_id = c.course_id
            WHERE c.instructor_id = ? ";
        
        $count_params = [$instructor['instructor_id']];
        
        if ($rating_filter !== null) {
            $count_query .= "AND cr.rating = ? ";
            $count_params[] = $rating_filter;
        }
        
        $count_query .= "AND cr.review_text IS NOT NULL AND cr.review_text != '' ";
        
        $stmt = $conn->prepare($count_query);
        $types = str_repeat('i', count($count_params));
        $stmt->bind_param($types, ...$count_params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $total_filtered_reviews = $result['total'];
        
        $total_pages = ceil($total_filtered_reviews / $limit);
        
    } catch (Exception $e) {
        echo "Error fetching instructor reviews: " . $e->getMessage();
        exit;
    }
} else {
    header("Location: 404.php");
    exit;
}
?>

<!-- Breadcrumb -->
<div class="bg-light">
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="instructors.php">Instructors</a></li>
                <li class="breadcrumb-item"><a href="instructor-profile.php?username=<?php echo $instructor['username']; ?>"><?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Reviews</li>
            </ol>
        </nav>
    </div>
</div>
<!-- End Breadcrumb -->

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <!-- Content -->
    <div class="container content-space-1 content-space-md-2">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-header-title">Student Reviews</h1>
                </div>
                <!-- End Col -->
            </div>
            <!-- End Row -->
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-lg-3">
                <!-- Instructor Summary Card -->
                <div class="card mb-3 mb-lg-5">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="flex-shrink-0">
                                <img class="avatar avatar-lg avatar-circle" 
                                     src="../uploads/instructor-profile/<?php echo !empty($instructor['profile_pic']) ? htmlspecialchars($instructor['profile_pic']) : 'default.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-0">
                                    <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
                                    <?php if ($instructor['verification_status'] === 'verified'): ?>
                                        <i class="bi-patch-check-fill text-primary" title="Verified Instructor"></i>
                                    <?php endif; ?>
                                </h4>
                                <span class="d-block text-body small">
                                    Instructor
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="mb-0"><?php echo number_format($instructor['average_rating'], 1); ?></h3>
                            <div class="d-flex gap-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= round($instructor['average_rating'])): ?>
                                        <img src="../assets/svg/illustrations/star.svg" alt="Star" width="16">
                                    <?php else: ?>
                                        <img src="../assets/svg/illustrations/star-half.svg" alt="Star" width="16">
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="small mb-4"><?php echo number_format($total_reviews); ?> reviews</p>

                        <!-- Rating Breakdown -->
                        <div class="mb-4">
                            <?php foreach ([5, 4, 3, 2, 1] as $rating): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="d-flex gap-1 me-2">
                                        <?php echo $rating; ?> <img src="../assets/svg/illustrations/star.svg" alt="Star" width="12">
                                    </div>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $ratings_percentages[$rating]; ?>%;" 
                                             aria-valuenow="<?php echo $ratings_percentages[$rating]; ?>" 
                                             aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <span class="small"><?php echo number_format($ratings_distribution[$rating]); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Filter By Rating -->
                        <div class="mb-4">
                            <h5 class="mb-3">Filter by Rating</h5>
                            <div class="d-grid gap-2">
                                <a href="?username=<?php echo urlencode($instructor['username']); ?>&sort=<?php echo $sort; ?>" 
                                   class="btn btn-sm <?php echo $rating_filter === null ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    All Reviews
                                </a>
                                <?php foreach ([5, 4, 3, 2, 1] as $rating): ?>
                                    <a href="?username=<?php echo urlencode($instructor['username']); ?>&rating=<?php echo $rating; ?>&sort=<?php echo $sort; ?>" 
                                       class="btn btn-sm <?php echo $rating_filter === $rating ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        <?php echo $rating; ?> Star<?php echo $rating !== 1 ? 's' : ''; ?> 
                                        (<?php echo number_format($ratings_distribution[$rating]); ?>)
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Sort Options -->
                        <div>
                            <h5 class="mb-3">Sort by</h5>
                            <div class="d-grid gap-2">
                                <?php
                                $sort_options = [
                                    'newest' => 'Newest First',
                                    'oldest' => 'Oldest First',
                                    'highest' => 'Highest Rated',
                                    'lowest' => 'Lowest Rated'
                                ];
                                ?>
                                <?php foreach ($sort_options as $sort_key => $sort_label): ?>
                                    <a href="?username=<?php echo urlencode($instructor['username']); ?><?php echo $rating_filter !== null ? '&rating=' . $rating_filter : ''; ?>&sort=<?php echo $sort_key; ?>" 
                                       class="btn btn-sm <?php echo $sort === $sort_key ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        <?php echo $sort_label; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Instructor Summary Card -->
            </div>
            <!-- End Col -->

            <div class="col-lg-9">
                <!-- Reviews -->
                <?php if (!empty($reviews)): ?>
                    <!-- Comment -->
                    <ul class="list-comment list-comment-divider mb-5">
                        <?php foreach ($reviews as $review): ?>
                            <!-- Item -->
                            <li class="list-comment-item">
                                <!-- Media -->
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($review['profile_pic'])): ?>
                                            <img class="avatar avatar-circle" src="../uploads/profile-pics/<?php echo htmlspecialchars($review['profile_pic']); ?>" alt="User">
                                        <?php else: ?>
                                            <div class="avatar avatar-soft-primary avatar-circle">
                                                <span class="avatar-initials"><?php echo substr($review['first_name'], 0, 1) . substr($review['last_name'], 0, 1); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h5 class="mb-0"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h5>
                                            <span class="d-block small text-muted"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="d-flex gap-1 me-2">
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <?php if ($i < $review['rating']): ?>
                                                        <img src="../assets/svg/illustrations/star.svg" alt="Review rating" width="16">
                                                    <?php else: ?>
                                                        <img src="../assets/svg/illustrations/star-muted.svg" alt="Review rating" width="16">
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <p class="mb-3"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                        </div>
                                        
                                        <!-- Course Link -->
                                        <div class="d-flex align-items-center border-top pt-3">
                                            <div class="flex-shrink-0 me-3">
                                                <?php
                                                $thumbnailPath = !empty($review['thumbnail']) ?
                                                    '../uploads/thumbnails/' . htmlspecialchars($review['thumbnail']) :
                                                    '../assets/svg/components/placeholder-img.svg';
                                                ?>
                                                <img src="<?php echo $thumbnailPath; ?>" alt="Course thumbnail" class="avatar avatar-xss avatar-4by3">
                                            </div>
                                            <div class="flex-grow-1 text-truncate">
                                                <a href="course-overview.php?id=<?php echo $review['course_id']; ?>" class="text-body small">
                                                    <span class="fw-semibold">Review for:</span> <?php echo htmlspecialchars($review['course_title']); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Media -->
                            </li>
                            <!-- End Item -->
                        <?php endforeach; ?>
                    </ul>
                    <!-- End Comment -->
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?username=<?php echo urlencode($instructor['username']); ?><?php echo $rating_filter !== null ? '&rating=' . $rating_filter : ''; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($start_page + 4, $total_pages);
                                if ($end_page - $start_page < 4 && $start_page > 1) {
                                    $start_page = max(1, $end_page - 4);
                                }
                                ?>
                                
                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?username=<?php echo urlencode($instructor['username']); ?><?php echo $rating_filter !== null ? '&rating=' . $rating_filter : ''; ?>&sort=<?php echo $sort; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?username=<?php echo urlencode($instructor['username']); ?><?php echo $rating_filter !== null ? '&rating=' . $rating_filter : ''; ?>&sort=<?php echo $sort; ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    <!-- End Pagination -->
                    
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <img class="mb-4" src="../assets/svg/illustrations/oc-empty-reviews.svg" alt="No reviews found" style="width: 20%;">
                            <h3>No reviews found</h3>
                            <?php if ($rating_filter !== null): ?>
                                <p class="text-muted">There are no <?php echo $rating_filter; ?>-star reviews for this instructor.</p>
                                <a href="?username=<?php echo urlencode($instructor['username']); ?>" class="btn btn-primary">View all reviews</a>
                            <?php else: ?>
                                <p class="text-muted">This instructor hasn't received any reviews yet.</p>
                                <a href="instructor-profile.php?username=<?php echo urlencode($instructor['username']); ?>" class="btn btn-primary">Return to profile</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- End Reviews -->
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->
    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/student-footer.php'; ?>