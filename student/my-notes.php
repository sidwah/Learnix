<?php
// student/my-notes.php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Include header
include '../includes/student-header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  // Redirect to login if not logged in
  header("Location: ../index.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Connect to database
require_once '../backend/config.php';

// Fetch all courses the student is enrolled in for filtering
$courses_query = "SELECT c.course_id, c.title, c.thumbnail 
                 FROM courses c 
                 JOIN enrollments e ON c.course_id = e.course_id 
                 WHERE e.user_id = ? AND e.status = 'Active'
                 ORDER BY c.title ASC";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses_result = $stmt->get_result();
$courses = [];
while ($course = $courses_result->fetch_assoc()) {
  $courses[] = $course;
}

// Get filters from GET parameters
$course_filter = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build the main query with filters
$notes_query = "SELECT 
                sn.note_id, 
                sn.content, 
                sn.timestamp, 
                sn.created_at,
                sn.updated_at,
                st.topic_id,
                st.title as topic_title,
                cs.section_id,
                cs.title as section_title,
                c.course_id,
                c.title as course_title,
                c.thumbnail as course_thumbnail,
                tc.content_type,
                tc.title as content_title
                FROM student_notes sn
                JOIN section_topics st ON sn.topic_id = st.topic_id
                JOIN course_sections cs ON st.section_id = cs.section_id
                JOIN courses c ON cs.course_id = c.course_id
                LEFT JOIN topic_content tc ON st.topic_id = tc.topic_id
                JOIN enrollments e ON c.course_id = e.course_id AND e.user_id = ?
                WHERE sn.user_id = ?";

// Add filters to query
$params = [$user_id, $user_id];
$types = "ii";

if ($course_filter > 0) {
  $notes_query .= " AND c.course_id = ?";
  $params[] = $course_filter;
  $types .= "i";
}

if (!empty($search_term)) {
  $search_term = "%$search_term%";
  $notes_query .= " AND (sn.content LIKE ? OR st.title LIKE ? OR cs.title LIKE ? OR c.title LIKE ?)";
  $params[] = $search_term;
  $params[] = $search_term;
  $params[] = $search_term;
  $params[] = $search_term;
  $types .= "ssss";
}

if (!empty($date_from)) {
  $notes_query .= " AND DATE(sn.updated_at) >= ?";
  $params[] = $date_from;
  $types .= "s";
}

if (!empty($date_to)) {
  $notes_query .= " AND DATE(sn.updated_at) <= ?";
  $params[] = $date_to;
  $types .= "s";
}

// Add sorting
switch ($sort_by) {
  case 'oldest':
    $notes_query .= " ORDER BY sn.updated_at ASC";
    break;
  case 'course':
    $notes_query .= " ORDER BY c.title ASC, cs.title ASC, st.title ASC";
    break;
  case 'section':
    $notes_query .= " ORDER BY cs.title ASC, st.title ASC";
    break;
  case 'topic':
    $notes_query .= " ORDER BY st.title ASC";
    break;
  case 'newest':
  default:
    $notes_query .= " ORDER BY sn.updated_at DESC";
    break;
}

$stmt = $conn->prepare($notes_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$notes_result = $stmt->get_result();

// Organize notes by course, section, and topic for hierarchical display
$organized_notes = [];
$notes_count = 0;

while ($note = $notes_result->fetch_assoc()) {
  $course_id = $note['course_id'];
  $section_id = $note['section_id'];
  $topic_id = $note['topic_id'];

  if (!isset($organized_notes[$course_id])) {
    $organized_notes[$course_id] = [
      'course_title' => $note['course_title'],
      'course_thumbnail' => $note['course_thumbnail'],
      'sections' => []
    ];
  }

  if (!isset($organized_notes[$course_id]['sections'][$section_id])) {
    $organized_notes[$course_id]['sections'][$section_id] = [
      'section_title' => $note['section_title'],
      'topics' => []
    ];
  }

  $organized_notes[$course_id]['sections'][$section_id]['topics'][$topic_id] = $note;
  $notes_count++;
}

// Function to get content type icon
function getContentTypeIcon($content_type)
{
  switch ($content_type) {
    case 'video':
      return 'bi-play-circle-fill';
    case 'text':
      return 'bi-file-text-fill';
    case 'link':
      return 'bi-link-45deg';
    case 'document':
      return 'bi-file-earmark-fill';
    default:
      return 'bi-journal-text';
  }
}

// Function to count words in a string
function countWords($string)
{
  return str_word_count(strip_tags($string));
}

// Function to get a summary of text
function getSummary($text, $maxLength = 150)
{
  $text = strip_tags($text);
  if (strlen($text) > $maxLength) {
    $text = substr($text, 0, $maxLength) . '...';
  }
  return $text;
}

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
  header('Content-Type: application/json');

  // Delete note
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_note') {
    $note_id = intval($_POST['note_id']);

    // Check if note belongs to user
    $check_query = "SELECT * FROM student_notes WHERE note_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $note_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
      echo json_encode(['success' => false, 'message' => 'Note not found or access denied']);
      exit();
    }

    // Delete the note
    $delete_query = "DELETE FROM student_notes WHERE note_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $note_id);
    $success = $stmt->execute();

    echo json_encode(['success' => $success]);
    exit();
  }

  // Export notes as PDF
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'export_notes') {
    $note_ids = json_decode($_POST['note_ids'], true);

    if (empty($note_ids)) {
      echo json_encode(['success' => false, 'message' => 'No notes selected']);
      exit();
    }

    // Placeholder for PDF export logic
    // In a real implementation, you would use a library like TCPDF or MPDF
    // to generate a PDF with the selected notes

    echo json_encode(['success' => true, 'message' => 'Export functionality would be implemented here']);
    exit();
  }
}
?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main" class="bg-light">
  <!-- Breadcrumb -->
  <div class="navbar-dark bg-dark" style="background-image: url(../assets/svg/components/wave-pattern-light.svg);">
    <div class="container content-space-1 content-space-b-lg-3">
      <div class="row align-items-center">
        <div class="col">
          <div class="d-none d-lg-block">
            <h1 class="h2 text-white">My Notes</h1>
          </div>

          <!-- Breadcrumb -->
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-light mb-0">
              <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
              <li class="breadcrumb-item active" aria-current="page">My Notes</li>
            </ol>
          </nav>
          <!-- End Breadcrumb -->
        </div>
        <!-- End Col -->

        <div class="col-auto">
          <div class="d-flex align-items-center gap-3">
            <span class="d-flex align-items-center gap-1">
              <span class="legend-indicator bg-info"></span>
              <span class="text-white-70 small">
                <i class="bi-journal-text me-1"></i>
                <?php echo $notes_count; ?> Notes
              </span>
            </span>
          </div>
        </div>
      </div>
      <!-- End Row -->
    </div>
  </div>
  <!-- End Breadcrumb -->

  <!-- Content -->
  <div class="container content-space-1 content-space-t-lg-0 content-space-b-lg-2 mt-lg-n10">
    <div class="row">
      <div class="col-lg-3">
        <!-- Navbar -->
        <div class="navbar-expand-lg navbar-light">
          <div id="sidebarNav" class="collapse navbar-collapse navbar-vertical">
            <!-- Card -->
            <div class="card flex-grow-1 mb-5">
              <div class="card-body">
                <!-- Avatar -->
                <div class="d-none d-lg-block text-center mb-5">
                  <div class="avatar avatar-xxl avatar-circle mb-3">
                    <div class="flex-shrink-0">
                      <img class="avatar avatar-xl avatar-circle"
                        src="../uploads/profile/<?php echo isset($row['profile_pic']) ? $row['profile_pic'] : 'default.png'; ?>"
                        alt="Profile">
                    </div>
                  </div>
                  <h4 class="card-title mb-0">
                    <?php echo isset($row['first_name']) && isset($row['last_name']) ? $row['first_name'] . ' ' . $row['last_name'] : 'Student'; ?>
                  </h4>
                  <p class="card-text small"><?php echo isset($row['email']) ? $row['email'] : $_SESSION['email']; ?></p>
                </div>
                <!-- End Avatar -->

                <!-- Sidebar Content -->

                <!-- Overview Section -->
                <span class="text-cap">Overview</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="account-overview.php">
                      <i class="bi-person-circle nav-icon"></i> Account Overview
                    </a>
                  </li>
                </ul>

                <!-- Account Section -->
                <span class="text-cap">Account</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="account-profile.php">
                      <i class="bi-person-badge nav-icon"></i> Personal info
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="account-security.php">
                      <i class="bi-shield-shaded nav-icon"></i> Security
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="account-notifications.php">
                      <i class="bi-bell nav-icon"></i> Notifications
                      <span class="badge bg-soft-dark text-dark rounded-pill nav-link-badge">0</span>
                    </a>
                  </li>
                </ul>

                <!-- Student-Specific Section -->
                <span class="text-cap">My Courses</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="my-courses.php">
                      <i class="bi-person-badge nav-icon"></i> Enrolled Courses
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="my-badges.php">
                      <i class="bi-chat-dots nav-icon"></i> Badges
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="my-certifications.php">
                      <i class="bi-award nav-icon"></i> Certifications
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link active" href="my-notes.php">
                      <i class="bi-journal-text nav-icon"></i> Notes
                    </a>
                  </li>
                </ul>

                <!-- Payment Section for Students -->
                <span class="text-cap">Payments</span>
                <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                  <li class="nav-item">
                    <a class="nav-link" href="payment-history.php">
                      <i class="bi-credit-card nav-icon"></i> Payment History
                    </a>
                  </li>
                </ul>

                <!-- Instructor/Admin Section (Dynamic Role Check) -->
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'instructor')): ?>
                  <span class="text-cap">Instructor</span>
                  <ul class="nav nav-sm nav-tabs nav-vertical mb-4">
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-courses.php">
                        <i class="bi-person-badge nav-icon"></i> My Courses
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="create-course.php">
                        <i class="bi-file-earmark-plus nav-icon"></i> Create Course
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="manage-students.php">
                        <i class="bi-person-check nav-icon"></i> Manage Students
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="course-feedback.php">
                        <i class="bi-chat-dots nav-icon"></i> Course Feedback
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-withdrawal.php">
                        <i class="bi-wallet nav-icon"></i> Withdrawal
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="instructor-analytics.php">
                        <i class="bi-gear nav-icon"></i> Analytics
                      </a>
                    </li>
                  </ul>
                <?php endif; ?>

                <!-- Sign-out & Help Section -->
                <span class="text-cap">---</span>
                <ul class="nav nav-sm nav-tabs nav-vertical">
                  <li class="nav-item">
                    <a class="nav-link" href="account-help.php">
                      <i class="bi-question-circle nav-icon"></i> Help
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="FAQ.php">
                      <i class="bi-card-list nav-icon"></i> FAQ's
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="report.php">
                      <i class="bi-exclamation-triangle nav-icon"></i> Report Issues
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="../backend/signout.php">
                      <i class="bi-box-arrow-right nav-icon"></i> Sign out
                    </a>
                  </li>
                </ul>

                <!-- End of Sidebar -->

              </div>
            </div>
            <!-- End Card -->
          </div>
        </div>
        <!-- End Navbar -->
      </div>
      <!-- End Col -->

      <div class="col-lg-9">
        <div class="d-grid gap-3 gap-lg-5">
          <!-- Card -->
          <div class="card">
            <div class="card-header border-bottom">
              <h4 class="card-header-title">Notes Management</h4>
            </div>

            <!-- Body -->
            <div class="card-body">
              <!-- Filters Section -->
              <form action="my-notes.php" method="GET" class="mb-4">
                <div class="row g-3">
                  <div class="col-sm-6 col-md-4">
                    <label class="form-label" for="courseFilter">Course</label>
                    <select class="form-select form-select-sm" name="course_id" id="courseFilter">
                      <option value="0">All Courses</option>
                      <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['course_id']; ?>" <?php echo ($course_filter == $course['course_id']) ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($course['title']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-sm-6 col-md-3">
                    <label class="form-label" for="dateFrom">From Date</label>
                    <input type="date" class="form-control form-control-sm" id="dateFrom" name="date_from" value="<?php echo $date_from; ?>">
                  </div>

                  <div class="col-sm-6 col-md-3">
                    <label class="form-label" for="dateTo">To Date</label>
                    <input type="date" class="form-control form-control-sm" id="dateTo" name="date_to" value="<?php echo $date_to; ?>">
                  </div>

                  <div class="col-sm-6 col-md-2">
                    <label class="form-label" for="sortBy">Sort By</label>
                    <select class="form-select form-select-sm" name="sort" id="sortBy">
                      <option value="newest" <?php echo ($sort_by == 'newest') ? 'selected' : ''; ?>>Newest</option>
                      <option value="oldest" <?php echo ($sort_by == 'oldest') ? 'selected' : ''; ?>>Oldest</option>
                      <option value="course" <?php echo ($sort_by == 'course') ? 'selected' : ''; ?>>Course</option>
                      <option value="section" <?php echo ($sort_by == 'section') ? 'selected' : ''; ?>>Section</option>
                      <option value="topic" <?php echo ($sort_by == 'topic') ? 'selected' : ''; ?>>Topic</option>
                    </select>
                  </div>

                  <div class="col-sm-9 col-md-10">
                    <label class="form-label" for="searchNotes">Search Notes</label>
                    <div class="input-group input-group-sm">
                      <input type="text" class="form-control" id="searchNotes" name="search" placeholder="Search in your notes..." value="<?php echo htmlspecialchars($search_term); ?>">
                      <button type="submit" class="btn btn-primary">
                        <i class="bi-search"></i>
                      </button>
                    </div>
                  </div>

                  <div class="col-sm-3 col-md-2">
                    <label class="form-label" for="resetFilters">&nbsp;</label>
                    <div class="d-grid">
                      <a href="my-notes.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                  </div>
                </div>
              </form>

              <!-- Action Buttons -->
              <div class="d-flex flex-wrap gap-2 mb-4">
                <div class="btn-group">
                  <button id="viewToggleBtn" class="btn btn-sm btn-outline-primary active" data-view="card">
                    <i class="bi-grid-3x3-gap-fill me-1"></i> Card View
                  </button>
                  <button id="viewToggleListBtn" class="btn btn-sm btn-outline-primary" data-view="list">
                    <i class="bi-list-ul me-1"></i> List View
                  </button>
                </div>

                <div class="ms-auto">
                  <div class="btn-group">
                    <button id="selectAllBtn" class="btn btn-sm btn-outline-primary">
                      <i class="bi-check-all me-1"></i> Select All
                    </button>
                    <button id="deselectAllBtn" class="btn btn-sm btn-outline-secondary" disabled>
                      <i class="bi-x-lg me-1"></i> Deselect All
                    </button>
                  </div>
                  <button id="batchExportBtn" class="btn btn-sm btn-soft-primary ms-2" disabled>
                    <i class="bi-file-earmark-pdf me-1"></i> Export Selected
                  </button>
                  <button id="batchPrintBtn" class="btn btn-sm btn-soft-info ms-2" disabled>
                    <i class="bi-printer me-1"></i> Print Selected
                  </button>
                </div>
              </div>

              <!-- Notes Container -->
              <div id="notesContainer">
                <!-- Empty State -->
                <?php if ($notes_count === 0): ?>
                  <div class="text-center py-5">
                    <div class="mb-3">
                      <i class="bi-journal-text text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5>No Notes Found</h5>
                    <p class="text-muted">
                      <?php if (!empty($search_term) || $course_filter > 0 || !empty($date_from) || !empty($date_to)): ?>
                        No notes match your current filters. Try adjusting your search or filters.
                      <?php else: ?>
                        You haven't created any notes yet. When you take notes while studying, they'll appear here.
                      <?php endif; ?>
                    </p>
                    <div class="mt-3">
                      <?php if (!empty($search_term) || $course_filter > 0 || !empty($date_from) || !empty($date_to)): ?>
                        <a href="my-notes.php" class="btn btn-primary">Clear Filters</a>
                      <?php else: ?>
                        <a href="courses.php" class="btn btn-primary">Go to My Courses</a>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php else: ?>

                  <!-- Card View (default) -->
                  <div id="cardView" class="card-view">
                    <?php foreach ($organized_notes as $course_id => $course_data): ?>
                      <div class="course-container mb-4">
                        <!-- Course header -->
                        <div class="d-flex align-items-center mb-3">
                          <div class="flex-shrink-0">
                            <div class="avatar avatar-sm avatar-circle">
                              <img class="avatar-img" src="../uploads/thumbnails/<?php echo htmlspecialchars($course_data['course_thumbnail'] ?: 'default-course.jpg'); ?>" alt="<?php echo htmlspecialchars($course_data['course_title']); ?>">
                            </div>
                          </div>
                          <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0">
                              <a href="course-materials.php?course_id=<?php echo $course_id; ?>" class="text-dark"><?php echo htmlspecialchars($course_data['course_title']); ?></a>
                            </h5>
                            <span class="d-block small text-muted">
                              <?php
                              $section_count = count($course_data['sections']);
                              $topic_count = 0;
                              foreach ($course_data['sections'] as $section) {
                                $topic_count += count($section['topics']);
                              }
                              echo "$section_count Sections • $topic_count Notes";
                              ?>
                            </span>
                          </div>
                          <div class="flex-shrink-0">
                            <button class="btn btn-sm btn-ghost-secondary course-toggle" data-course-id="<?php echo $course_id; ?>">
                              <i class="bi-chevron-down chevron-icon"></i>
                            </button>
                          </div>
                        </div>

                        <!-- Course notes content -->
                        <div class="course-content" id="course-<?php echo $course_id; ?>">
                          <?php foreach ($course_data['sections'] as $section_id => $section_data): ?>
                            <div class="section-container mb-3">
                              <!-- Section header -->
                              <div class="d-flex align-items-center py-2 px-3 bg-soft-primary rounded-2">
                                <i class="bi-collection me-2"></i>
                                <div class="flex-grow-1">
                                  <h6 class="mb-0"><?php echo htmlspecialchars($section_data['section_title']); ?></h6>
                                  <span class="d-block small"><?php echo count($section_data['topics']); ?> Notes</span>
                                </div>
                                <div class="flex-shrink-0">
                                  <button class="btn btn-xs btn-ghost-secondary section-toggle" data-section-id="<?php echo $section_id; ?>">
                                    <i class="bi-chevron-down"></i>
                                  </button>
                                </div>
                              </div>

                              <!-- Section topics with notes -->
                              <div class="section-content pt-2" id="section-<?php echo $section_id; ?>">
                                <div class="row g-3">
                                  <?php foreach ($section_data['topics'] as $topic_id => $note): ?>
                                    <div class="col-md-6 col-xl-4">
                                      <div class="card h-100 note-card" data-note-id="<?php echo $note['note_id']; ?>">
                                        <div class="card-header border-bottom">
                                          <div class="d-flex align-items-center">
                                            <i class="<?php echo getContentTypeIcon($note['content_type']); ?> text-primary me-2"></i>
                                            <div class="flex-grow-1 text-truncate">
                                              <h6 class="card-header-title" title="<?php echo htmlspecialchars($note['topic_title']); ?>">
                                                <?php echo htmlspecialchars($note['topic_title']); ?>
                                              </h6>
                                            </div>
                                            <div class="flex-shrink-0">
                                              <div class="form-check mb-0">
                                                <input class="form-check-input note-checkbox" type="checkbox" id="note-check-<?php echo $note['note_id']; ?>" data-note-id="<?php echo $note['note_id']; ?>">
                                              </div>
                                            </div>
                                          </div>
                                        </div>

                                        <div class="card-body">
                                          <div class="note-content mb-3" style="max-height: 150px; overflow: hidden;">
                                            <?php echo getSummary($note['content']); ?>
                                          </div>
                                          <div class="d-flex align-items-center small">
                                            <span class="me-auto text-muted">
                                              <i class="bi-clock me-1"></i>
                                              <?php echo date('M j, Y', strtotime($note['updated_at'])); ?>
                                            </span>
                                            <span class="text-muted me-2" title="Word Count">
                                              <i class="bi-text-paragraph me-1"></i>
                                              <?php echo countWords($note['content']); ?>
                                            </span>
                                          </div>
                                        </div>

                                        <div class="card-footer border-top bg-light">
                                          <div class="d-flex gap-2">
                                            <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $topic_id; ?>#notes" class="btn btn-xs btn-soft-primary flex-fill">
                                              <i class="bi-pencil me-1"></i> Edit
                                            </a>
                                            <button class="btn btn-xs btn-soft-secondary flex-fill view-note-btn" data-note-id="<?php echo $note['note_id']; ?>" data-bs-toggle="modal" data-bs-target="#viewNoteModal">
                                              <i class="bi-eye me-1"></i> View
                                            </button>
                                            <button class="btn btn-xs btn-soft-danger flex-fill delete-note-btn" data-note-id="<?php echo $note['note_id']; ?>">
                                              <i class="bi-trash me-1"></i> Delete
                                            </button>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  <?php endforeach; ?>
                                </div>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <!-- List View (hidden by default) -->
                  <div id="listView" class="list-view d-none">
                    <div class="table-responsive">
                      <table class="table table-hover">
                        <thead class="thead-light">
                          <tr>
                            <th width="3%">
                              <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="selectAllTableCheck">
                              </div>
                            </th>
                            <th width="20%">Course / Section</th>
                            <th width="20%">Topic</th>
                            <th width="32%">Note Preview</th>
                            <th width="12%">Last Updated</th>
                            <th width="13%">Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($organized_notes as $course_id => $course_data): ?>
                            <?php foreach ($course_data['sections'] as $section_id => $section_data): ?>
                              <?php foreach ($section_data['topics'] as $topic_id => $note): ?>
                                <tr class="note-row" data-note-id="<?php echo $note['note_id']; ?>">
                                  <td>
                                    <div class="form-check mb-0">
                                      <input class="form-check-input table-note-checkbox" type="checkbox" data-note-id="<?php echo $note['note_id']; ?>">
                                    </div>
                                  </td>
                                  <td>
                                    <div class="d-flex align-items-center">
                                      <div class="flex-shrink-0">
                                        <div class="avatar avatar-xs avatar-circle">
                                          <img class="avatar-img" src="../uploads/thumbnails/<?php echo htmlspecialchars($course_data['course_thumbnail'] ?: 'default-course.jpg'); ?>" alt="<?php echo htmlspecialchars($course_data['course_title']); ?>">
                                        </div>
                                      </div>
                                      <div class="flex-grow-1 ms-2">
                                        <h6 class="mb-0 text-truncate" style="max-width: 150px;">
                                          <a href="course-materials.php?course_id=<?php echo $course_id; ?>" class="text-dark">
                                            <?php echo htmlspecialchars($course_data['course_title']); ?>
                                          </a>
                                        </h6>
                                        <span class="d-block small text-muted text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($section_data['section_title']); ?></span>
                                      </div>
                                    </div>
                                  </td>
                                  <td>
                                    <div class="d-flex align-items-center">
                                      <i class="<?php echo getContentTypeIcon($note['content_type']); ?> text-primary me-2"></i>
                                      <span class="text-truncate" style="max-width: 180px;"><?php echo htmlspecialchars($note['topic_title']); ?></span>
                                    </div>
                                  </td>
                                  <td>
                                    <p class="text-truncate mb-0" style="max-width: 250px;">
                                      <?php echo getSummary($note['content'], 50); ?>
                                    </p>
                                    <span class="d-block small text-muted">
                                      <i class="bi-text-paragraph me-1"></i> <?php echo countWords($note['content']); ?> words
                                    </span>
                                  </td>
                                  <td>
                                    <span class="small text-muted">
                                      <?php echo date('M j, Y', strtotime($note['updated_at'])); ?>
                                    </span>
                                    <span class="d-block small text-muted">
                                      <?php echo date('g:i A', strtotime($note['updated_at'])); ?>
                                    </span>
                                  </td>
                                  <td>
                                    <div class="btn-group btn-group-sm">
                                      <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $topic_id; ?>#notes" class="btn btn-soft-primary btn-sm" title="Edit Note">
                                        <i class="bi-pencil"></i>
                                      </a>
                                      <button type="button" class="btn btn-soft-secondary btn-sm view-note-btn" data-note-id="<?php echo $note['note_id']; ?>" data-bs-toggle="modal" data-bs-target="#viewNoteModal" title="View Note">
                                        <i class="bi-eye"></i>
                                      </button>
                                      <button type="button" class="btn btn-soft-danger btn-sm delete-note-btn" data-note-id="<?php echo $note['note_id']; ?>" title="Delete Note">
                                        <i class="bi-trash"></i>
                                      </button>
                                    </div>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                            <?php endforeach; ?>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <!-- End Body -->
          </div>
          <!-- End Card -->
        </div>
      </div>
      <!-- End Col -->
    </div>
    <!-- End Row -->
  </div>
  <!-- End Content -->

  <!-- Note View Modal -->
  <div class="modal fade" id="viewNoteModal" tabindex="-1" aria-labelledby="viewNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewNoteModalLabel">Note Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="note-modal-content">
            <div class="mb-3 pb-3 border-bottom d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1" id="noteModalTopic">Topic Title</h6>
                <p class="mb-0 small text-muted" id="noteModalCourse">Course > Section</p>
              </div>
              <div class="text-end">
                <span class="badge bg-soft-primary text-primary rounded-pill" id="noteModalContentType">
                  <i class="bi-file-text me-1"></i> Text
                </span>
                <div class="small text-muted mt-1" id="noteModalDate">Last Updated: Feb 15, 2023</div>
              </div>
            </div>
            <div class="note-text-content py-2" id="noteModalContent" style="white-space: pre-wrap; max-height: 400px; overflow-y: auto;">
              Note content will appear here.
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="me-auto">
            <span class="small text-muted" id="noteModalWordCount">
              <i class="bi-text-paragraph me-1"></i> Word count: 150
            </span>
          </div>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="printNoteBtn">
            <i class="bi-printer me-1"></i> Print Note
          </button>
          <a href="#" class="btn btn-sm btn-primary" id="editNoteLink">
            <i class="bi-pencil me-1"></i> Edit Note
          </a>
          <button type="button" class="btn btn-sm btn-outline-danger" id="modalDeleteNoteBtn">
            <i class="bi-trash me-1"></i> Delete
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this note? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
      </div>
    </div>
  </div>
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- Custom JavaScript for Notes Page -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
        // View toggle functionality
        const viewToggleBtn = document.getElementById('viewToggleBtn');
        const viewToggleListBtn = document.getElementById('viewToggleListBtn');
        const cardView = document.getElementById('cardView');
        const listView = document.getElementById('listView');

        if (viewToggleBtn && viewToggleListBtn && cardView && listView) {
          viewToggleBtn.addEventListener('click', function() {
            cardView.classList.remove('d-none');
            listView.classList.add('d-none');
            viewToggleBtn.classList.add('active');
            viewToggleListBtn.classList.remove('active');
          });

          viewToggleListBtn.addEventListener('click', function() {
            cardView.classList.add('d-none');
            listView.classList.remove('d-none');
            viewToggleBtn.classList.remove('active');
            viewToggleListBtn.classList.add('active');
          });
        }

        // Course and Section toggle functionality
        const courseToggles = document.querySelectorAll('.course-toggle');
        const sectionToggles = document.querySelectorAll('.section-toggle');

        courseToggles.forEach(toggle => {
          toggle.addEventListener('click', function() {
            const courseId = this.getAttribute('data-course-id');
            const courseContent = document.getElementById('course-' + courseId);
            const icon = this.querySelector('.chevron-icon');

            if (courseContent.style.display === 'none') {
              courseContent.style.display = 'block';
              icon.classList.remove('bi-chevron-right');
              icon.classList.add('bi-chevron-down');
            } else {
              courseContent.style.display = 'none';
              icon.classList.remove('bi-chevron-down');
              icon.classList.add('bi-chevron-right');
            }
          });
        });

        sectionToggles.forEach(toggle => {
          toggle.addEventListener('click', function() {
            const sectionId = this.getAttribute('data-section-id');
            const sectionContent = document.getElementById('section-' + sectionId);
            const icon = this.querySelector('i');

            if (sectionContent.style.display === 'none') {
              sectionContent.style.display = 'block';
              icon.classList.remove('bi-chevron-right');
              icon.classList.add('bi-chevron-down');
            } else {
              sectionContent.style.display = 'none';
              icon.classList.remove('bi-chevron-down');
              icon.classList.add('bi-chevron-right');
            }
          });
        });

        // Note selection and batch operations
        const selectAllBtn = document.getElementById('selectAllBtn');
        const deselectAllBtn = document.getElementById('deselectAllBtn');
        const batchExportBtn = document.getElementById('batchExportBtn');
        const batchPrintBtn = document.getElementById('batchPrintBtn');
        const selectAllTableCheck = document.getElementById('selectAllTableCheck');

        // Note checkboxes in card view
        const noteCheckboxes = document.querySelectorAll('.note-checkbox');
        // Note checkboxes in table view
        const tableNoteCheckboxes = document.querySelectorAll('.table-note-checkbox');

        // Function to update button states based on selection
        function updateButtonStates() {
          const selectedNotes = document.querySelectorAll('.note-checkbox:checked, .table-note-checkbox:checked');
          if (selectedNotes.length > 0) {
            deselectAllBtn.disabled = false;
            batchExportBtn.disabled = false;
            batchPrintBtn.disabled = false;
          } else {
            deselectAllBtn.disabled = true;
            batchExportBtn.disabled = true;
            batchPrintBtn.disabled = true;
          }
        }

        // Select all functionality
        if (selectAllBtn) {
          selectAllBtn.addEventListener('click', function() {
            noteCheckboxes.forEach(checkbox => {
              checkbox.checked = true;
            });

            tableNoteCheckboxes.forEach(checkbox => {
              checkbox.checked = true;
            });

            if (selectAllTableCheck) {
              selectAllTableCheck.checked = true;
            }

            updateButtonStates();
          });
        }

        // Deselect all functionality
        if (deselectAllBtn) {
          deselectAllBtn.addEventListener('click', function() {
            noteCheckboxes.forEach(checkbox => {
              checkbox.checked = false;
            });

            tableNoteCheckboxes.forEach(checkbox => {
              checkbox.checked = false;
            });

            if (selectAllTableCheck) {
              selectAllTableCheck.checked = false;
            }

            updateButtonStates();
          });
        }

        // Table header checkbox functionality
        if (selectAllTableCheck) {
          selectAllTableCheck.addEventListener('change', function() {
            tableNoteCheckboxes.forEach(checkbox => {
              checkbox.checked = this.checked;
            });

            updateButtonStates();
          });
        }

        // Individual checkbox change
        noteCheckboxes.forEach(checkbox => {
          checkbox.addEventListener('change', updateButtonStates);
        });

        tableNoteCheckboxes.forEach(checkbox => {
          checkbox.addEventListener('change', updateButtonStates);
        });

        // Batch export functionality
        if (batchExportBtn) {
          batchExportBtn.addEventListener('click', function() {
            const selectedNoteIds = Array.from(document.querySelectorAll('.note-checkbox:checked, .table-note-checkbox:checked'))
              .map(checkbox => checkbox.getAttribute('data-note-id'));

            if (selectedNoteIds.length === 0) {
              alert('Please select at least one note to export.');
              return;
            }

            // Call the export function with the selected notes
            exportNotes(selectedNoteIds);
          });
        }

        // Batch print functionality
        if (batchPrintBtn) {
          batchPrintBtn.addEventListener('click', function() {
            const selectedNoteIds = Array.from(document.querySelectorAll('.note-checkbox:checked, .table-note-checkbox:checked'))
              .map(checkbox => checkbox.getAttribute('data-note-id'));

            if (selectedNoteIds.length === 0) {
              alert('Please select at least one note to print.');
              return;
            }

            // Prepare print window
            printNotes(selectedNoteIds);
          });
        }

        // Function to export selected notes as PDF
        function exportNotes(noteIds) {
          fetch('my-notes.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: `action=export_notes&note_ids=${JSON.stringify(noteIds)}`
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // In a real implementation, this would trigger a download
                alert('Export functionality would be implemented with a library like TCPDF or MPDF.');
              } else {
                alert('Error exporting notes: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('An error occurred while trying to export notes.');
            });
        }

        // Function to print selected notes
// Function to print selected notes
function printNotes(noteIds) {
  // Get note data for printing
  const noteElements = noteIds.map(id => {
    const cardElement = document.querySelector('.note-card[data-note-id="' + id + '"]');
    const rowElement = document.querySelector('.note-row[data-note-id="' + id + '"]');
    
    let noteData = {};
    
    if (cardElement) {
      const title = cardElement.querySelector('.card-header-title').textContent.trim();
      const content = cardElement.querySelector('.note-content').textContent.trim();
      const courseTitle = cardElement.closest('.course-container').querySelector('h5').textContent.trim();
      const sectionTitle = cardElement.closest('.section-container').querySelector('h6').textContent.trim();
      const date = cardElement.querySelector('.text-muted').textContent.trim();
      
      noteData = { title, content, courseTitle, sectionTitle, date };
    } else if (rowElement) {
      const title = rowElement.querySelector('td:nth-child(3) .text-truncate').textContent.trim();
      const content = 'Note content not available in list view';
      const courseTitle = rowElement.querySelector('td:nth-child(2) h6').textContent.trim();
      const sectionTitle = rowElement.querySelector('td:nth-child(2) span').textContent.trim();
      const date = rowElement.querySelector('td:nth-child(5) span:first-child').textContent.trim() + ' ' + 
                   rowElement.querySelector('td:nth-child(5) span:last-child').textContent.trim();
      
      noteData = { title, content, courseTitle, sectionTitle, date };
    }
    
    return noteData;
  });
  
  // Create print window
  const printWindow = window.open('', '_blank');
  
  // Generate HTML for printing using string concatenation instead of template literals
  let printContent = '<!DOCTYPE html>\n<html>\n<head>\n';
  printContent += '<title>My Notes - Print</title>\n';
  printContent += '<style>\n';
  printContent += 'body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }\n';
  printContent += '.print-header { text-align: center; margin-bottom: 30px; padding-bottom: 10px; border-bottom: 1px solid #ccc; }\n';
  printContent += '.note { margin-bottom: 30px; page-break-inside: avoid; }\n';
  printContent += '.note-header { margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid #eee; }\n';
  printContent += '.note-title { font-size: 16px; font-weight: bold; margin-bottom: 5px; }\n';
  printContent += '.note-context { font-size: 12px; color: #666; margin-bottom: 5px; }\n';
  printContent += '.note-date { font-size: 12px; color: #999; }\n';
  printContent += '.note-content { white-space: pre-wrap; margin-top: 10px; }\n';
  printContent += '@media print { .print-header { position: fixed; top: 0; width: 100%; background: white; }\n';
  printContent += '.content { margin-top: 50px; }\n';
  printContent += '@page { margin: 2cm; } }\n';
  printContent += '</style>\n</head>\n<body>\n';
  printContent += '<div class="print-header">\n';
  printContent += '<h1>My Notes</h1>\n';
  printContent += '<p>Printed on ' + new Date().toLocaleDateString() + '</p>\n';
  printContent += '</div>\n<div class="content">\n';
  
  // Add each note to the print content
  noteElements.forEach(function(note, index) {
    printContent += '<div class="note">\n';
    printContent += '<div class="note-header">\n';
    printContent += '<div class="note-title">' + note.title + '</div>\n';
    printContent += '<div class="note-context">' + note.courseTitle + ' &gt; ' + note.sectionTitle + '</div>\n';
    printContent += '<div class="note-date">' + note.date + '</div>\n';
    printContent += '</div>\n';
    printContent += '<div class="note-content">' + note.content + '</div>\n';
    printContent += '</div>\n';
    
    // Add page break for all except the last note
    if (index < noteElements.length - 1) {
      printContent += '<div style="page-break-after: always;"></div>\n';
    }
  });
  
  printContent += '</div>\n';
  printContent += '<script>\n';
  printContent += 'window.onload = function() {\n';
  printContent += '  window.print();\n';
  printContent += '  setTimeout(function() { window.close(); }, 500);\n';
  printContent += '}\n';
  printContent += '<\\/script>\n';
  printContent += '</body>\n</html>';
  
  printWindow.document.open();
  printWindow.document.write(printContent);
  printWindow.document.close();
}


// View note in modal
const viewNoteBtns = document.querySelectorAll('.view-note-btn');
const viewNoteModal = document.getElementById('viewNoteModal');

if (viewNoteBtns.length > 0 && viewNoteModal) {
viewNoteBtns.forEach(btn => {
btn.addEventListener('click', function() {
const noteId = this.getAttribute('data-note-id');
let noteData = {};

// Find the note card or row
const noteCard = document.querySelector(`.note-card[data-note-id="${noteId}"]`);
const noteRow = document.querySelector(`.note-row[data-note-id="${noteId}"]`);

if (noteCard) {
// Get data from card view
const topicTitle = noteCard.querySelector('.card-header-title').textContent.trim();
const noteContent = noteCard.querySelector('.note-content').textContent.trim();
const courseTitle = noteCard.closest('.course-container').querySelector('h5').textContent.trim();
const sectionTitle = noteCard.closest('.section-container').querySelector('h6').textContent.trim();
const updatedDate = noteCard.querySelector('.text-muted').textContent.trim();
const wordCount = noteCard.querySelector('.text-muted:last-child').textContent.trim();
const contentTypeIcon = noteCard.querySelector('.card-header i').className;

noteData = {
topicTitle,
noteContent,
courseTitle,
sectionTitle,
updatedDate,
wordCount,
contentTypeIcon
};

// Get the course_id and topic_id for the edit link
const courseId = noteCard.closest('.course-container').querySelector('h5 a').getAttribute('href').split('=')[1];
const topicId = noteCard.querySelector('a').getAttribute('href').split('&topic=')[1].split('#')[0];
noteData.editLink = `course-content.php?course_id=${courseId}&topic=${topicId}#notes`;

} else if (noteRow) {
// Get data from list view
const topicTitle = noteRow.querySelector('td:nth-child(3) span').textContent.trim();
const noteContent = noteRow.querySelector('td:nth-child(4) p').textContent.trim();
const courseTitle = noteRow.querySelector('td:nth-child(2) h6 a').textContent.trim();
const sectionTitle = noteRow.querySelector('td:nth-child(2) span').textContent.trim();
const updatedDate = noteRow.querySelector('td:nth-child(5) span:first-child').textContent.trim() + ' ' +
noteRow.querySelector('td:nth-child(5) span:last-child').textContent.trim();
const wordCount = noteRow.querySelector('td:nth-child(4) span').textContent.trim();
const contentTypeIcon = noteRow.querySelector('td:nth-child(3) i').className;

noteData = {
topicTitle,
noteContent,
courseTitle,
sectionTitle,
updatedDate,
wordCount,
contentTypeIcon
};

// Get the course_id and topic_id for the edit link
const editLink = noteRow.querySelector('td:nth-child(6) a').getAttribute('href');
noteData.editLink = editLink;
}

// Populate modal with note data
document.getElementById('noteModalTopic').textContent = noteData.topicTitle;
document.getElementById('noteModalCourse').textContent = `${noteData.courseTitle} > ${noteData.sectionTitle}`;
document.getElementById('noteModalContent').textContent = noteData.noteContent;
document.getElementById('noteModalDate').textContent = `Last Updated: ${noteData.updatedDate}`;
document.getElementById('noteModalWordCount').textContent = noteData.wordCount;
document.getElementById('editNoteLink').setAttribute('href', noteData.editLink);

// Set content type badge
const contentTypeBadge = document.getElementById('noteModalContentType');

if (noteData.contentTypeIcon.includes('play-circle')) {
contentTypeBadge.innerHTML = '<i class="bi-play-circle me-1"></i> Video';
} else if (noteData.contentTypeIcon.includes('file-text')) {
contentTypeBadge.innerHTML = '<i class="bi-file-text me-1"></i> Text';
} else if (noteData.contentTypeIcon.includes('link')) {
contentTypeBadge.innerHTML = '<i class="bi-link me-1"></i> Link';
} else if (noteData.contentTypeIcon.includes('file-earmark')) {
contentTypeBadge.innerHTML = '<i class="bi-file-earmark me-1"></i> Document';
} else {
contentTypeBadge.innerHTML = '<i class="bi-journal-text me-1"></i> Note';
}

// Set up delete button in modal to use the same note ID
document.getElementById('modalDeleteNoteBtn').setAttribute('data-note-id', noteId);
});
});
}

// Print single note from modal
const printNoteBtn = document.getElementById('printNoteBtn');
// Print single note from modal
if (printNoteBtn) {
  printNoteBtn.addEventListener('click', function() {
    const topic = document.getElementById('noteModalTopic').textContent;
    const course = document.getElementById('noteModalCourse').textContent;
    const content = document.getElementById('noteModalContent').textContent;
    const date = document.getElementById('noteModalDate').textContent;
    
    const printWindow = window.open('', '_blank');
    
    // Use string concatenation instead of template literals
    let printContent = '<!DOCTYPE html>\n<html>\n<head>\n';
    printContent += '<title>Note: ' + topic + '</title>\n';
    printContent += '<style>\n';
    printContent += 'body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }\n';
    printContent += '.print-header { text-align: center; margin-bottom: 30px; padding-bottom: 10px; border-bottom: 1px solid #ccc; }\n';
    printContent += '.note-header { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }\n';
    printContent += '.note-title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }\n';
    printContent += '.note-context { font-size: 14px; color: #666; margin-bottom: 5px; }\n';
    printContent += '.note-date { font-size: 12px; color: #999; }\n';
    printContent += '.note-content { white-space: pre-wrap; margin-top: 20px; }\n';
    printContent += '@media print { @page { margin: 2cm; } }\n';
    printContent += '</style>\n</head>\n<body>\n';
    printContent += '<div class="print-header">\n';
    printContent += '<h1>Note Details</h1>\n';
    printContent += '<p>Printed on ' + new Date().toLocaleDateString() + '</p>\n';
    printContent += '</div>\n';
    printContent += '<div class="note-header">\n';
    printContent += '<div class="note-title">' + topic + '</div>\n';
    printContent += '<div class="note-context">' + course + '</div>\n';
    printContent += '<div class="note-date">' + date + '</div>\n';
    printContent += '</div>\n';
    printContent += '<div class="note-content">' + content + '</div>\n';
    printContent += '<scrip>\n';
    printContent += 'window.onload = function() {\n';
    printContent += '  window.print();\n';
    printContent += '  setTimeout(function() { window.close(); }, 500);\n';
    printContent += '}\n';
    printContent += '<\\/script>\n';
    printContent += '</body>\n</html>';
    
    printWindow.document.open();
    printWindow.document.write(printContent);
    printWindow.document.close();
  });
}

// Delete note functionality
const deleteNoteBtns = document.querySelectorAll('.delete-note-btn, #modalDeleteNoteBtn');
const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

let noteToDelete = null;

deleteNoteBtns.forEach(btn => {
btn.addEventListener('click', function() {
noteToDelete = this.getAttribute('data-note-id');
deleteConfirmModal.show();
});
});

if (confirmDeleteBtn) {
confirmDeleteBtn.addEventListener('click', function() {
if (noteToDelete) {
deleteNote(noteToDelete);
}
});
}

function deleteNote(noteId) {
fetch('my-notes.php', {
method: 'POST',
headers: {
'Content-Type': 'application/x-www-form-urlencoded',
'X-Requested-With': 'XMLHttpRequest'
},
body: `action=delete_note&note_id=${noteId}`
})
.then(response => response.json())
.then(data => {
if (data.success) {
// Close any open modals
deleteConfirmModal.hide();
const viewNoteModalElem = document.getElementById('viewNoteModal');
if (viewNoteModalElem) {
const viewNoteModal = bootstrap.Modal.getInstance(viewNoteModalElem);
if (viewNoteModal) {
viewNoteModal.hide();
}
}

// Remove the note card and row from DOM
const noteCard = document.querySelector(`.note-card[data-note-id="${noteId}"]`);
const noteRow = document.querySelector(`.note-row[data-note-id="${noteId}"]`);

if (noteCard) {
const noteCol = noteCard.closest('.col-md-6');
if (noteCol) {
noteCol.remove();
}
}

if (noteRow) {
noteRow.remove();
}

// Show success message
showToast('Success', 'Note has been deleted successfully');

// Update note count
updateNoteCount();
} else {
showToast('Error', data.message || 'Failed to delete note');
}
})
.catch(error => {
console.error('Error:', error);
showToast('Error', 'An error occurred while trying to delete the note');
});
}

// Show toast notification
function showToast(title, message) {
const toastContainer = document.createElement('div');
toastContainer.className = 'position-fixed top-0 end-0 p-3';
toastContainer.style.zIndex = '1080';

const toastHtml = `
<div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
  <div class="toast-header">
    <strong class="me-auto">${title}</strong>
    <small>Just now</small>
    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
  <div class="toast-body">
    ${message}
  </div>
</div>
`;

toastContainer.innerHTML = toastHtml;
document.body.appendChild(toastContainer);

const toastElement = toastContainer.querySelector('.toast');
const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
toast.show();

// Clean up after toast is hidden
toastElement.addEventListener('hidden.bs.toast', function() {
document.body.removeChild(toastContainer);
});
}

// Update note count after deletion
function updateNoteCount() {
const remainingNotes = document.querySelectorAll('.note-card').length;
const noteCountElem = document.querySelector('.text-white-70.small');

if (noteCountElem) {
noteCountElem.innerHTML = `<i class="bi-journal-text me-1"></i> ${remainingNotes} Notes`;
}

// Check if we need to show empty state
if (remainingNotes === 0) {
const notesContainer = document.getElementById('notesContainer');
if (notesContainer) {
notesContainer.innerHTML = `
<div class="text-center py-5">
  <div class="mb-3">
    <i class="bi-journal-text text-primary" style="font-size: 3rem;"></i>
  </div>
  <h5>No Notes Found</h5>
  <p class="text-muted">
    You haven't created any notes yet. When you take notes while studying, they'll appear here.
  </p>
  <div class="mt-3">
    <a href="courses.php" class="btn btn-primary">Go to My Courses</a>
  </div>
</div>
`;
}
}
}
});
</script>

<?php
// Include footer
include '../includes/student-footer.php';
?>