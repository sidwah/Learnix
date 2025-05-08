<?php
// Set the current page for the active menu item
$page = 'Courses';


// Simulate fetching courses  (replace with actual query)
$courses = [
    [
        'id' => 1,
        'title' => 'Python Programming',
        'instructors' => [
            ['id' => 1, 'name' => 'Dr. Alice Smith', 'avatar' => 'default.png'],
            ['id' => 2, 'name' => 'Prof. Bob Johnson', 'avatar' => 'default.png']
        ],
        'created_date' => '2025-02-15',
        'updated_date' => '2025-04-20',
        'status' => 'published',
        'is_featured' => true,
        'enrolled_students' => 342,
        'completion_rate' => 68.5,
        'rating' => 4.8,
        'modules_count' => 8,
        'content_items' => 24,
        'thumbnail' => 'python-course.jpg',
        'description' => 'Learn Python programming from scratch. This comprehensive course covers basic syntax, data structures, control flow, functions, and more advanced concepts like object-oriented programming.',
        'duration' => '24 hours',
        'modules' => [
            [
                'title' => 'Introduction to Python',
                'topics' => [
                    ['title' => 'Getting Started with Python', 'type' => 'video', 'duration' => '15:20'],
                    ['title' => 'Installing Python and Setting Up IDE', 'type' => 'text', 'duration' => '10 min read'],
                    ['title' => 'Your First Python Program', 'type' => 'interactive', 'duration' => '20 min']
                ]
            ],
            [
                'title' => 'Python Basics',
                'topics' => [
                    ['title' => 'Variables and Data Types', 'type' => 'video', 'duration' => '18:45'],
                    ['title' => 'Basic Operators', 'type' => 'text', 'duration' => '12 min read'],
                    ['title' => 'Input and Output', 'type' => 'video', 'duration' => '14:30'],
                    ['title' => 'Module 1 Quiz', 'type' => 'quiz', 'duration' => '15 min']
                ]
            ],
            [
                'title' => 'Control Flow',
                'topics' => [
                    ['title' => 'Conditional Statements', 'type' => 'video', 'duration' => '22:15'],
                    ['title' => 'Loops in Python', 'type' => 'video', 'duration' => '20:40'],
                    ['title' => 'Control Flow Practice', 'type' => 'interactive', 'duration' => '25 min'],
                    ['title' => 'Module 2 Quiz', 'type' => 'quiz', 'duration' => '15 min']
                ]
            ]
        ]
    ],
    [
        'id' => 2,
        'title' => 'Data Science 101',
        'instructors' => [], // Empty array for draft courses - no instructors assigned yet
        'created_date' => '2025-01-10',
        'updated_date' => '2025-04-18',
        'status' => 'draft',
        'is_featured' => false,
        'enrolled_students' => 0,
        'completion_rate' => 0,
        'rating' => 0,
        'modules_count' => 3,
        'content_items' => 10,
        'thumbnail' => 'data-science.jpg',
        'description' => 'Introduction to data science concepts and techniques. Learn about data analysis, visualization, statistical methods, and machine learning basics.',
        'duration' => '18 hours',
        'modules' => [
            [
                'title' => 'Introduction to Data Science',
                'topics' => [
                    ['title' => 'What is Data Science?', 'type' => 'video', 'duration' => '12:30'],
                    ['title' => 'Data Science Process', 'type' => 'text', 'duration' => '15 min read']
                ]
            ],
            [
                'title' => 'Data Analysis Fundamentals',
                'topics' => [
                    ['title' => 'Descriptive Statistics', 'type' => 'video', 'duration' => '20:15'],
                    ['title' => 'Data Cleaning Techniques', 'type' => 'interactive', 'duration' => '25 min']
                ]
            ]
        ]
    ],
    [
        'id' => 3,
        'title' => 'Network Security',
        'instructors' => [
            ['id' => 4, 'name' => 'Prof. David Lee', 'avatar' => 'default.png'],
            ['id' => 5, 'name' => 'Dr. Emma Brown', 'avatar' => 'default.png']
        ],
        'created_date' => '2024-11-05',
        'updated_date' => '2025-04-15',
        'status' => 'published',
        'is_featured' => true,
        'enrolled_students' => 256,
        'completion_rate' => 77.2,
        'rating' => 4.5,
        'modules_count' => 6,
        'content_items' => 18,
        'thumbnail' => 'network-security.jpg',
        'description' => 'Learn essential network security principles, protocols, and practices. This course covers firewalls, encryption, threat detection, and security policies.',
        'duration' => '32 hours'
    ],
    [
        'id' => 4,
        'title' => 'Mobile App Development',
        'instructors' => [], // Empty array for draft courses - no instructors assigned yet
        'created_date' => '2025-03-12',
        'updated_date' => '2025-04-19',
        'status' => 'draft',
        'is_featured' => false,
        'enrolled_students' => 0,
        'completion_rate' => 0,
        'rating' => 0,
        'modules_count' => 2,
        'content_items' => 8,
        'thumbnail' => 'mobile-dev.jpg',
        'description' => 'Introduction to mobile application development. Learn to create apps for iOS and Android platforms.',
        'duration' => '28 hours'
    ],
    [
        'id' => 5,
        'title' => 'Web Design Fundamentals',
        'instructors' => [
            ['id' => 7, 'name' => 'Dr. James Taylor', 'avatar' => 'default.png']
        ],
        'created_date' => '2024-12-20',
        'updated_date' => '2025-04-17',
        'status' => 'maintenance',
        'is_featured' => false,
        'enrolled_students' => 120,
        'completion_rate' => 45.8,
        'rating' => 4.2,
        'modules_count' => 5,
        'content_items' => 15,
        'thumbnail' => 'web-design.jpg',
        'description' => 'Learn the fundamentals of web design including HTML, CSS, responsive design, and usability principles.',
        'duration' => '20 hours'
    ],
    [
        'id' => 6,
        'title' => 'Artificial Intelligence Ethics',
        'instructors' => [
            ['id' => 8, 'name' => 'Dr. Maria Rodriguez', 'avatar' => 'default.png']
        ],
        'created_date' => '2024-09-10',
        'updated_date' => '2025-04-16',
        'status' => 'archived',
        'is_featured' => false,
        'enrolled_students' => 185,
        'completion_rate' => 92.7,
        'rating' => 4.9,
        'modules_count' => 4,
        'content_items' => 14,
        'thumbnail' => 'ai-ethics.jpg',
        'description' => 'Explore the ethical considerations in artificial intelligence development and deployment. Case studies from real-world AI systems.',
        'duration' => '16 hours'
    ],
    [
        'id' => 7,
        'title' => 'Digital Marketing Strategies',
        'instructors' => [
            ['id' => 9, 'name' => 'Prof. Michael Chen', 'avatar' => 'default.png']
        ],
        'created_date' => '2024-10-15',
        'updated_date' => '2025-04-14',
        'status' => 'unpublished',
        'is_featured' => false,
        'enrolled_students' => 98,
        'completion_rate' => 35.2,
        'rating' => 3.8,
        'modules_count' => 7,
        'content_items' => 21,
        'thumbnail' => 'digital-marketing.jpg',
        'description' => 'Learn effective digital marketing strategies across various platforms. SEO, content marketing, social media, and analytics.',
        'duration' => '25 hours'
    ],
    [
        'id' => 8,
        'title' => 'Blockchain Technology',
        'instructors' => [
            ['id' => 10, 'name' => 'Dr. Thomas Wright', 'avatar' => 'default.png']
        ],
        'created_date' => '2024-08-22',
        'updated_date' => '2025-04-13',
        'status' => 'archived',
        'is_featured' => false,
        'enrolled_students' => 210,
        'completion_rate' => 88.5,
        'rating' => 4.7,
        'modules_count' => 6,
        'content_items' => 20,
        'thumbnail' => 'blockchain.jpg',
        'description' => 'Comprehensive introduction to blockchain technology, cryptocurrencies, smart contracts, and decentralized applications.',
        'duration' => '22 hours'
    ]
];

// Calculate status counts for the dashboard cards
$statusCounts = [
    'draft' => 0,
    'published' => 0,
    'unpublished' => 0,
    'maintenance' => 0,
    'archived' => 0
];

foreach ($courses as $course) {
    if (isset($statusCounts[$course['status']])) {
        $statusCounts[$course['status']]++;
    }
}

// Include the header
include_once('../includes/admin/header.php');

// Include the sidebar
include_once('../includes/admin/sidebar.php');

// Include the navbar
include_once('../includes/admin/navbar.php');
?>

<!-- Custom CSS for dropdown positioning and other styling -->
<style>
  /* Fix for dropdown menu to appear outside table without affecting layout */
  .table-responsive .dropdown-menu {
    position: absolute;
    right: 0;
    left: auto;
    transform: none !important;
    top: 100%;
    z-index: 1000;
  }
  
  /* Status card styling */
  .status-card {
    transition: transform 0.3s ease;
    cursor: pointer;
  }
  
  .status-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }
  
  /* Card icon styling */
  .status-card .card-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
  }
  
  /* Toast placement */
  .toast-placement-ex {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 1080;
  }
  
  /* Pagination custom styles */
  .pagination-sm .page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
  }
  
  /* Course type badges */
  .content-type-badge {
    border-radius: 50rem;
    padding: 0.35rem 0.65rem;
    font-size: 0.75rem;
    font-weight: 500;
  }
  
  /* Status indicators */
  .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
  }
  
  /* Filter active state */
  .filter-badge.active {
    background-color: #696cff !important;
    color: #fff !important;
  }
  
  /* Hide rows based on filter */
  tr.filtered {
    display: none;
  }
  
  /* Featured toggle switch */
  .form-switch .form-check-input:checked {
    background-color: #696cff;
    border-color: #696cff;
  }
  
  /* Course thumbnail */
  .course-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 0.375rem;
  }
  
  /* Course info in table */
  .course-info {
    display: flex;
    align-items: center;
  }
  
  .course-stats {
    display: flex;
    align-items: center;
    font-size: 0.75rem;
  }
  
  .course-stats i {
    font-size: 0.875rem;
    margin-right: 0.25rem;
  }
  
  .course-stats span {
    margin-right: 0.75rem;
  }
  
  /* Course analytics modal */
  .analytics-card {
    transition: all 0.3s ease;
  }
  
  .analytics-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
  }
  
  .rating-stars {
    color: #ffc107;
  }
  
  /* Course preview modal styles */
  .preview-header {
    background-color: #f8f9fa;
    border-radius: 0.375rem 0.375rem 0 0;
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
  }
  
  .preview-header .course-title {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
  }
  
  .preview-header .course-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    margin-top: 0.5rem;
    font-size: 0.875rem;
  }
  
  .preview-header .course-meta-item {
    margin-right: 1.5rem;
    display: flex;
    align-items: center;
  }
  
  .preview-header .course-meta-item i {
    margin-right: 0.5rem;
    font-size: 1rem;
  }
  
  .preview-thumbnail {
    width: 100%;
    height: 240px;
    object-fit: cover;
    border-radius: 0.375rem;
    margin-bottom: 1.5rem;
  }
  
  .preview-tabs .nav-link {
    color: #697a8d;
    font-weight: 500;
  }
  
  .preview-tabs .nav-link.active {
    color: #696cff;
    background-color: transparent;
    border-bottom: 2px solid #696cff;
  }
  
  .preview-module {
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
  }
  
  .preview-module-header {
    padding: 1rem 1.5rem;
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .preview-module-header .module-title {
    display: flex;
    align-items: center;
  }
  
  .preview-module-header .module-title i {
    margin-right: 0.5rem;
  }
  
  .preview-module-content {
    padding: 1rem 1.5rem;
  }
  
  .preview-topic {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .preview-topic:last-child {
    border-bottom: none;
  }
  
  .preview-topic-info {
    display: flex;
    align-items: center;
  }
  
  .preview-topic-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    flex-shrink: 0;
  }
  
  .preview-topic-duration {
    font-size: 0.75rem;
    color: #697a8d;
  }
  
  .type-video {
    background-color: #e7f5ff;
    color: #0c8af0;
  }
  
  .type-text {
    background-color: #e8f8f5;
    color: #1eac91;
  }
  
  .type-quiz {
    background-color: #fff2e2;
    color: #f59e0b;
  }
  
  .type-interactive {
    background-color: #e9e7fd;
    color: #896dff;
  }
  
  .type-assignment {
    background-color: #ffe2e5;
    color: #ff3e55;
  }
  
  .preview-instructor-card {
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
  }
  
  .preview-instructor-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 1rem;
  }
  
  .preview-instructor-info h6 {
    margin-bottom: 0.25rem;
  }
  
  .preview-instructor-info p {
    margin-bottom: 0;
    font-size: 0.875rem;
    color: #697a8d;
  }
  
  /* Maintenance status */
  .maintenance-badge {
    background-color: #ffe2e8;
    color: #ff3e6c;
  }
  
  .maintenance-dot {
    background-color: #ff3e6c;
  }
  
  .maintenance-icon {
    background-color: #ffe2e8;
    color: #ff3e6c;
  }
  
  .maintenance-alert {
    border-left: 4px solid #ff3e6c;
    background-color: #fff5f7;
  }
</style>

<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Toast Notification -->
    <div class="bs-toast toast toast-placement-ex m-2 fade bg-danger top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000" id="errorToast">
      <div class="toast-header">
        <i class="bx bx-bell me-2"></i>
        <div class="me-auto fw-semibold">Error</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" id="errorToastMessage"></div>
    </div>

    <div class="bs-toast toast toast-placement-ex m-2 fade bg-success top-0 end-0" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000" id="successToast">
      <div class="toast-header">
        <i class="bx bx-check me-2"></i>
        <div class="me-auto fw-semibold">Success</div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" id="successToastMessage"></div>
    </div>
    <!-- /Toast Notification -->
    
    <h4 class="fw-bold py-3 mb-4">
      <span class="text-muted fw-light">Administration /</span>
      Courses List
    </h4>

    <!-- Status Cards -->
    <div class="row mb-4">
      <!-- Draft Card -->
      <div class="col-lg col-md-6 col-sm-6 mb-3">
        <div class="card status-card h-100" data-filter="draft">
          <div class="card-body d-flex align-items-center">
            <div class="card-icon bg-label-warning me-3">
              <i class="bx bx-edit-alt fs-3"></i>
            </div>
            <div>
              <h5 class="card-title mb-0">Draft</h5>
              <p class="card-text fs-3 fw-semibold mb-0"><?php echo $statusCounts['draft']; ?></p>
              <small class="text-muted">Courses in development</small>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Published Card -->
      <div class="col-lg col-md-6 col-sm-6 mb-3">
        <div class="card status-card h-100" data-filter="published">
          <div class="card-body d-flex align-items-center">
            <div class="card-icon bg-label-success me-3">
              <i class="bx bx-check-circle fs-3"></i>
            </div>
            <div>
              <h5 class="card-title mb-0">Published</h5>
              <p class="card-text fs-3 fw-semibold mb-0"><?php echo $statusCounts['published']; ?></p>
              <small class="text-muted">Active courses</small>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Unpublished Card -->
      <div class="col-lg col-md-6 col-sm-6 mb-3">
        <div class="card status-card h-100" data-filter="unpublished">
          <div class="card-body d-flex align-items-center">
            <div class="card-icon bg-label-info me-3">
              <i class="bx bx-hide fs-3"></i>
            </div>
            <div>
              <h5 class="card-title mb-0">Unpublished</h5>
              <p class="card-text fs-3 fw-semibold mb-0"><?php echo $statusCounts['unpublished']; ?></p>
              <small class="text-muted">Inactive courses</small>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Maintenance Card -->
      <div class="col-lg col-md-6 col-sm-6 mb-3">
        <div class="card status-card h-100" data-filter="maintenance">
          <div class="card-body d-flex align-items-center">
            <div class="card-icon maintenance-icon me-3">
              <i class="bx bx-wrench fs-3"></i>
            </div>
            <div>
              <h5 class="card-title mb-0">Maintenance</h5>
              <p class="card-text fs-3 fw-semibold mb-0"><?php echo $statusCounts['maintenance']; ?></p>
              <small class="text-muted">Being updated</small>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Archived Card -->
      <div class="col-lg col-md-6 col-sm-6 mb-3">
        <div class="card status-card h-100" data-filter="archived">
          <div class="card-body d-flex align-items-center">
            <div class="card-icon bg-label-secondary me-3">
              <i class="bx bx-archive fs-3"></i>
            </div>
            <div>
              <h5 class="card-title mb-0">Archived</h5>
              <p class="card-text fs-3 fw-semibold mb-0"><?php echo $statusCounts['archived']; ?></p>
              <small class="text-muted">Completed courses</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Course Table -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Courses List</h5>
            <div class="d-flex align-items-center">
              <div class="me-3">
                <div class="input-group input-group-sm">
                  <span class="input-group-text"><i class="bx bx-search"></i></span>
                  <input type="text" class="form-control" id="courseSearch" placeholder="Search courses..." aria-label="Search">
                </div>
              </div>
              <div class="me-3">
                <div class="btn-group" role="group" aria-label="Filter courses">
                  <button type="button" class="btn btn-outline-secondary btn-sm filter-badge active" data-filter="all">
                    All
                  </button>
                  <button type="button" class="btn btn-outline-warning btn-sm filter-badge" data-filter="draft">
                    Draft
                  </button>
                  <button type="button" class="btn btn-outline-success btn-sm filter-badge" data-filter="published">
                    Published
                  </button>
                  <button type="button" class="btn btn-outline-danger btn-sm filter-badge" data-filter="maintenance">
                    Maintenance
                  </button>
                  <button type="button" class="btn btn-outline-info btn-sm filter-badge" data-filter="unpublished">
                    Unpublished
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-sm filter-badge" data-filter="archived">
                    Archived
                  </button>
                </div>
              </div>
              <a href="create-course.php" class="btn btn-primary btn-sm me-2">
                <i class="bx bx-plus me-1"></i> Create Course
              </a>
              <button type="button" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-export me-1"></i> Export
              </button>
            </div>
          </div>
          <div class="table-responsive text-nowrap">
            <table class="table" id="coursesTable">
              <thead>
                <tr>
                  <th>Course</th>
                  <th>Instructors</th>
                  <th>Status</th>
                  <th>Featured</th>
                  <th>Metrics</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody class="table-border-bottom-0">
                <?php 
                if (empty($courses)) {
                ?>
                <tr>
                  <td colspan="6" class="text-center py-3">
                    <div class="d-flex flex-column align-items-center py-4">
                      <i class="bx bx-book-alt mb-2" style="font-size: 3rem; color: #d9dee3;"></i>
                      <h6 class="mb-1">No courses found</h6>
                      <p class="text-muted mb-0">Start creating courses for your department</p>
                    </div>
                  </td>
                </tr>
                <?php
                } else {
                  foreach ($courses as $course) {
                    // Configure status display
                    $status_badge = [
                      'draft' => 'bg-label-warning',
                      'published' => 'bg-label-success',
                      'unpublished' => 'bg-label-info',
                      'maintenance' => 'maintenance-badge',
                      'archived' => 'bg-label-secondary'
                    ];
                    
                    $status_dot = [
                      'draft' => 'bg-warning',
                      'published' => 'bg-success',
                      'unpublished' => 'bg-info',
                      'maintenance' => 'maintenance-dot',
                      'archived' => 'bg-secondary'
                    ];
                    
                    $status_label = ucfirst($course['status']);
                ?>
                  <tr data-status="<?php echo $course['status']; ?>">
                    <td>
                      <div class="course-info">
                        <img src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                             alt="<?php echo htmlspecialchars($course['title']); ?>" 
                             class="course-thumbnail me-3" />
                        <div>
                          <strong><?php echo htmlspecialchars($course['title']); ?></strong>
                          <div class="small text-muted">
                            <span><i class="bx bx-book"></i> <?php echo $course['modules_count']; ?> modules</span>
                            <span><i class="bx bx-list-ul"></i> <?php echo $course['content_items']; ?> items</span>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <?php if ($course['status'] == 'draft') { ?>
                        <span class="text-muted">No instructors assigned</span>
                      <?php } else if (!empty($course['instructors'])) { ?>
                        <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
                          <?php foreach ($course['instructors'] as $instructor) { ?>
                            <li
                              data-bs-toggle="tooltip"
                              data-popup="tooltip-custom"
                              data-bs-placement="top"
                              class="avatar avatar-xs pull-up"
                              title="<?php echo htmlspecialchars($instructor['name']); ?>"
                            >
                              <img src="../uploads/instructor-profile/<?php echo htmlspecialchars($instructor['avatar']); ?>" alt="<?php echo htmlspecialchars($instructor['name']); ?>" class="rounded-circle" />
                            </li>
                          <?php } ?>
                        </ul>
                      <?php } else { ?>
                        <span class="text-muted">No instructors assigned</span>
                      <?php } ?>
                    </td>
                    <td>
                      <span class="badge <?php echo $status_badge[$course['status']]; ?>">
                        <span class="status-dot <?php echo $status_dot[$course['status']]; ?>"></span>
                        <?php echo $status_label; ?>
                      </span>
                    </td>
                    <td>
                      <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" <?php echo $course['is_featured'] ? 'checked' : ''; ?> 
                               onclick="toggleFeatured(<?php echo $course['id']; ?>, this.checked)" 
                               <?php echo $course['status'] == 'published' ? '' : 'disabled'; ?> />
                      </div>
                    </td>
                    <td>
                      <div class="course-stats">
                        <?php if ($course['status'] == 'draft') { ?>
                          <span class="text-muted">No metrics available</span>
                        <?php } else if ($course['status'] == 'maintenance') { ?>
                          <span title="Enrolled students"><i class="bx bx-user"></i> <?php echo $course['enrolled_students']; ?></span>
                          <span title="Maintenance"><i class="bx bx-time"></i> In progress</span>
                        <?php } else { ?>
                          <span title="Enrolled students"><i class="bx bx-user"></i> <?php echo $course['enrolled_students']; ?></span>
                          <span title="Completion rate"><i class="bx bx-chart"></i> <?php echo $course['completion_rate']; ?>%</span>
                          <?php if ($course['rating'] > 0) { ?>
                            <span title="Rating"><i class="bx bx-star"></i> <?php echo $course['rating']; ?></span>
                          <?php } ?>
                        <?php } ?>
                      </div>
                    </td>
                    <td>
                      <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                          <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu">
                          <a class="dropdown-item" href="edit-course.php?id=<?php echo $course['id']; ?>">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                          </a>
                          <a class="dropdown-item" href="javascript:void(0);" onclick="previewCourse(<?php echo $course['id']; ?>)">
                            <i class="bx bx-show me-1"></i> Preview
                          </a>
                          <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#analyticsModal<?php echo $course['id']; ?>">
                            <i class="bx bx-bar-chart-alt-2 me-1"></i> Analytics
                          </a>
                          <div class="dropdown-divider"></div>
                          
                          <?php if ($course['status'] == 'draft') { ?>
                            <a class="dropdown-item text-success" href="javascript:void(0);" onclick="updateStatus(<?php echo $course['id']; ?>, 'published')">
                              <i class="bx bx-check-circle me-1"></i> Publish
                            </a>
                          <?php } else if ($course['status'] == 'published') { ?>
                            <a class="dropdown-item text-info" href="javascript:void(0);" onclick="updateStatus(<?php echo $course['id']; ?>, 'unpublished')">
                              <i class="bx bx-hide me-1"></i> Unpublish
                            </a>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="openMaintenanceModal(<?php echo $course['id']; ?>)">
                              <i class="bx bx-wrench me-1"></i> Set to Maintenance
                            </a>
                          <?php } else if ($course['status'] == 'unpublished') { ?>
                            <a class="dropdown-item text-success" href="javascript:void(0);" onclick="updateStatus(<?php echo $course['id']; ?>, 'published')">
                              <i class="bx bx-check-circle me-1"></i> Publish
                            </a>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="openMaintenanceModal(<?php echo $course['id']; ?>)">
                              <i class="bx bx-wrench me-1"></i> Set to Maintenance
                            </a>
                          <?php } else if ($course['status'] == 'maintenance') { ?>
                            <a class="dropdown-item text-success" href="javascript:void(0);" onclick="updateStatus(<?php echo $course['id']; ?>, 'published')">
                              <i class="bx bx-check-circle me-1"></i> Return to Published
                            </a>
                            <a class="dropdown-item text-info" href="javascript:void(0);" onclick="updateStatus(<?php echo $course['id']; ?>, 'unpublished')">
                              <i class="bx bx-hide me-1"></i> Move to Unpublished
                            </a>
                          <?php } ?>
                          
                          <?php if ($course['status'] != 'archived') { ?>
                            <a class="dropdown-item text-secondary" href="javascript:void(0);" onclick="archiveCourse(<?php echo $course['id']; ?>)">
                              <i class="bx bx-archive me-1"></i> Archive
                            </a>
                          <?php } else { ?>
                            <a class="dropdown-item text-info" href="javascript:void(0);" onclick="updateStatus(<?php echo $course['id']; ?>, 'unpublished')">
                              <i class="bx bx-archive-out me-1"></i> Unarchive
                            </a>
                          <?php } ?>
                          
                          <a class="dropdown-item text-danger" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $course['id']; ?>">
                            <i class="bx bx-trash me-1"></i> Delete
                          </a>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php 
                  }
                } 
                ?>
              </tbody>
            </table>
          </div>
          <!-- Pagination -->
          <div class="card-footer">
            <div class="row">
              <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" id="pagination-info" role="status" aria-live="polite">
                  Showing <span id="showing-start">1</span> to <span id="showing-end">10</span> of <span id="total-entries"><?php echo count($courses); ?></span> entries
                </div>
              </div>
              <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers" id="pagination-container">
                  <ul class="pagination pagination-sm justify-content-end mb-0">
                    <li class="paginate_button page-item previous disabled" id="pagination-previous">
                      <a href="#" class="page-link">Previous</a>
                    </li>
                    <!-- Page numbers will be inserted dynamically with JavaScript -->
                    <li class="paginate_button page-item active">
                      <a href="#" class="page-link">1</a>
                    </li>
                    <li class="paginate_button page-item next" id="pagination-next">
                      <a href="#" class="page-link">Next</a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Course Preview Modals -->
    <?php foreach ($courses as $course) { ?>
      <div class="modal fade" id="previewModal<?php echo $course['id']; ?>" tabindex="-1" aria-labelledby="previewModalLabel<?php echo $course['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header p-0">
              <div class="preview-header w-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h5 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                  <span class="badge <?php echo $status_badge[$course['status']]; ?>"><?php echo ucfirst($course['status']); ?></span>
                </div>
                <p class="mb-2"><?php echo htmlspecialchars($course['description']); ?></p>
                
                <?php if ($course['status'] == 'maintenance') { ?>
                <div class="alert maintenance-alert mb-2">
                  <div class="d-flex">
                    <i class="bx bx-wrench me-2 mt-1"></i>
                    <span>This course is currently undergoing maintenance and updates. Access is limited.</span>
                  </div>
                </div>
                <?php } ?>
                
                <div class="course-meta">
                  <div class="course-meta-item">
                    <i class="bx bx-time"></i>
                    <span><?php echo htmlspecialchars($course['duration']); ?></span>
                  </div>
                  <div class="course-meta-item">
                    <i class="bx bx-book"></i>
                    <span><?php echo $course['modules_count']; ?> modules</span>
                  </div>
                  <div class="course-meta-item">
                    <i class="bx bx-list-ul"></i>
                    <span><?php echo $course['content_items']; ?> lessons</span>
                  </div>
                  <?php if ($course['status'] == 'published' && $course['rating'] > 0) { ?>
                  <div class="course-meta-item">
                    <i class="bx bx-star"></i>
                    <span><?php echo $course['rating']; ?> (<?php echo $course['enrolled_students']; ?> students)</span>
                  </div>
                  <?php } ?>
                </div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-8">
                  <img src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                       alt="<?php echo htmlspecialchars($course['title']); ?>" 
                       class="preview-thumbnail" />
                  
                  <!-- Tabs for Content, About, Requirements -->
                  <ul class="nav nav-tabs preview-tabs mb-3" role="tablist">
                    <li class="nav-item">
                      <button class="nav-link active" id="content-tab<?php echo $course['id']; ?>" data-bs-toggle="tab" 
                              data-bs-target="#content<?php echo $course['id']; ?>" type="button" role="tab" 
                              aria-controls="content<?php echo $course['id']; ?>" aria-selected="true">
                        Course Content
                      </button>
                    </li>
                    <li class="nav-item">
                      <button class="nav-link" id="about-tab<?php echo $course['id']; ?>" data-bs-toggle="tab" 
                              data-bs-target="#about<?php echo $course['id']; ?>" type="button" role="tab" 
                              aria-controls="about<?php echo $course['id']; ?>" aria-selected="false">
                        About
                      </button>
                    </li>
                    <li class="nav-item">
                      <button class="nav-link" id="requirements-tab<?php echo $course['id']; ?>" data-bs-toggle="tab" 
                              data-bs-target="#requirements<?php echo $course['id']; ?>" type="button" role="tab" 
                              aria-controls="requirements<?php echo $course['id']; ?>" aria-selected="false">
                        Requirements
                      </button>
                    </li>
                  </ul>
                  
                  <!-- Tab content -->
                  <div class="tab-content">
                    <!-- Course Content Tab -->
                    <div class="tab-pane fade show active" id="content<?php echo $course['id']; ?>" role="tabpanel" 
                         aria-labelledby="content-tab<?php echo $course['id']; ?>">
                      <?php if (empty($course['modules'])) { ?>
                        <div class="alert alert-info">
                          <div class="d-flex">
                            <i class="bx bx-info-circle me-2 mt-1"></i>
                            <span>This course doesn't have any content yet.</span>
                          </div>
                        </div>
                      <?php } else { ?>
                        <div class="accordion" id="moduleAccordion<?php echo $course['id']; ?>">
                          <?php 
                          if (isset($course['modules'])) {
                            foreach ($course['modules'] as $index => $module) { 
                          ?>
                            <div class="preview-module">
                              <div class="preview-module-header" data-bs-toggle="collapse" 
                                   data-bs-target="#moduleCollapse<?php echo $course['id']; ?>-<?php echo $index; ?>">
                                <div class="module-title">
                                  <i class="bx bx-collection"></i>
                                  <span>Module <?php echo $index + 1; ?>: <?php echo htmlspecialchars($module['title']); ?></span>
                                </div>
                                <div class="module-meta">
                                  <span class="badge bg-label-primary"><?php echo count($module['topics']); ?> topics</span>
                                  <i class="bx bx-chevron-down ms-2"></i>
                                </div>
                              </div>
                              <div id="moduleCollapse<?php echo $course['id']; ?>-<?php echo $index; ?>" 
                                   class="collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                   data-bs-parent="#moduleAccordion<?php echo $course['id']; ?>">
                                <div class="preview-module-content">
                                  <?php foreach ($module['topics'] as $topic) { 
                                    // Configure topic type display
                                    $topic_icons = [
                                      'video' => 'bx-play-circle',
                                      'text' => 'bx-file',
                                      'quiz' => 'bx-check-square',
                                      'interactive' => 'bx-joystick',
                                      'assignment' => 'bx-task'
                                    ];
                                    
                                    $icon = isset($topic_icons[$topic['type']]) ? $topic_icons[$topic['type']] : 'bx-file';
                                  ?>
                                    <div class="preview-topic">
                                      <div class="preview-topic-info">
                                        <div class="preview-topic-icon type-<?php echo $topic['type']; ?>">
                                          <i class="bx <?php echo $icon; ?>"></i>
                                        </div>
                                        <div>
                                          <div class="fw-semibold"><?php echo htmlspecialchars($topic['title']); ?></div>
                                          <div class="preview-topic-duration">
                                            <i class="bx bx-time fs-xs"></i> <?php echo htmlspecialchars($topic['duration']); ?>
                                          </div>
                                        </div>
                                      </div>
                                      <div>
                                        <span class="badge bg-label-<?php echo $topic['type'] == 'video' ? 'primary' : 
                                                                          ($topic['type'] == 'text' ? 'success' : 
                                                                          ($topic['type'] == 'quiz' ? 'warning' : 
                                                                          ($topic['type'] == 'interactive' ? 'info' : 'danger'))); ?>">
                                          <?php echo ucfirst($topic['type']); ?>
                                        </span>
                                      </div>
                                    </div>
                                  <?php } ?>
                                </div>
                              </div>
                            </div>
                          <?php 
                            }
                          }
                          ?>
                        </div>
                      <?php } ?>
                    </div>
                    
                    <!-- About Tab -->
                    <div class="tab-pane fade" id="about<?php echo $course['id']; ?>" role="tabpanel" 
                         aria-labelledby="about-tab<?php echo $course['id']; ?>">
                      <div class="mb-4">
                        <h6 class="fw-semibold mb-2">Course Description</h6>
                        <p><?php echo htmlspecialchars($course['description']); ?></p>
                      </div>
                      
                      <div class="mb-4">
                        <h6 class="fw-semibold mb-2">What You'll Learn</h6>
                        <ul class="list-group list-group-flush">
                          <li class="list-group-item p-0 pb-2">
                            <i class="bx bx-check text-success me-2"></i> Master fundamental concepts and techniques
                          </li>
                          <li class="list-group-item p-0 pb-2">
                            <i class="bx bx-check text-success me-2"></i> Build real-world applications
                          </li>
                          <li class="list-group-item p-0 pb-2">
                            <i class="bx bx-check text-success me-2"></i> Understand advanced principles
                          </li>
                          <li class="list-group-item p-0 pb-2">
                            <i class="bx bx-check text-success me-2"></i> Complete hands-on projects and exercises
                          </li>
                        </ul>
                      </div>
                      
                      <div>
                        <h6 class="fw-semibold mb-2">Course Information</h6>
                        <ul class="list-group list-group-flush">
                          <li class="list-group-item p-0 pb-2 d-flex justify-content-between">
                            <span>Last Updated</span>
                            <span class="fw-semibold"><?php echo htmlspecialchars($course['updated_date']); ?></span>
                          </li>
                          <li class="list-group-item p-0 pb-2 d-flex justify-content-between">
                            <span>Total Duration</span>
                            <span class="fw-semibold"><?php echo htmlspecialchars($course['duration']); ?></span>
                          </li>
                          <li class="list-group-item p-0 pb-2 d-flex justify-content-between">
                            <span>Language</span>
                            <span class="fw-semibold">English</span>
                          </li>
                          <li class="list-group-item p-0 pb-2 d-flex justify-content-between">
                            <span>Certificate</span>
                            <span class="fw-semibold">Yes, upon completion</span>
                          </li>
                        </ul>
                      </div>
                    </div>
                    
                    <!-- Requirements Tab -->
                    <div class="tab-pane fade" id="requirements<?php echo $course['id']; ?>" role="tabpanel" 
                         aria-labelledby="requirements-tab<?php echo $course['id']; ?>">
                      <div class="mb-4">
                        <h6 class="fw-semibold mb-2">Prerequisites</h6>
                        <ul class="list-group list-group-flush">
                          <li class="list-group-item p-0 pb-2">
                            <i class="bx bx-right-arrow-alt text-primary me-2"></i> Basic computer knowledge
                          </li>
                          <li class="list-group-item p-0 pb-2">
                            <i class="bx bx-right-arrow-alt text-primary me-2"></i> No prior programming experience required
                          </li>
                          <li class="list-group-item p-0 pb-2">
                            <i class="bx bx-right-arrow-alt text-primary me-2"></i> A computer with internet access
                          </li>
                        </ul>
                      </div>
                      
                      <div>
                        <h6 class="fw-semibold mb-2">Technical Requirements</h6>
                        <ul class="list-group list-group-flush">
                          <li class="list-group-item p-0 pb-2">
                            <i class="bx bx-laptop text-primary me-2"></i> Any operating system (Windows, macOS, Linux)
                          </li>
                          <li class="list-group-item p-0 pb-2">
                            <i class="bx bx-code-alt text-primary me-2"></i> Required software will be covered in the course
                          </li>
                          <li class="list-group-item p-0 pb-2">
                            <i class="bx bx-world text-primary me-2"></i> Stable internet connection
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-4">
                  <div class="card mb-4">
                    <div class="card-body">
                      <h6 class="fw-semibold mb-3">Course Instructors</h6>
                      <?php if ($course['status'] == 'draft') { ?>
                        <div class="alert alert-warning">
                          <div class="d-flex">
                            <i class="bx bx-info-circle me-2"></i>
                            <div>No instructors have been assigned to this course yet. Instructors will be added when the course exits the draft stage.</div>
                          </div>
                        </div>
                      <?php } else if (!empty($course['instructors'])) { ?>
                        <?php foreach ($course['instructors'] as $instructor) { ?>
                          <div class="preview-instructor-card">
                            <img src="../uploads/instructor-profile/<?php echo htmlspecialchars($instructor['avatar']); ?>" 
                                 alt="<?php echo htmlspecialchars($instructor['name']); ?>" 
                                 class="preview-instructor-avatar" />
                            <div class="preview-instructor-info">
                              <h6><?php echo htmlspecialchars($instructor['name']); ?></h6>
                              <p>Professor of Computer Science</p>
                            </div>
                          </div>
                        <?php } ?>
                      <?php } else { ?>
                        <div class="alert alert-warning">
                          <div class="d-flex">
                            <i class="bx bx-info-circle me-2"></i>
                            <div>No instructors have been assigned to this course.</div>
                          </div>
                        </div>
                      <?php } ?>
                    </div>
                  </div>
                  
                  <div class="card">
                    <div class="card-body">
                      <h6 class="fw-semibold mb-3">Course Stats</h6>
                      <ul class="list-group list-group-flush">
                        <?php if ($course['status'] == 'draft') { ?>
                          <li class="list-group-item px-0">
                            <div class="alert alert-light mb-0">
                              <i class="bx bx-info-circle me-1"></i>
                              <span>Stats will be available after the course is published.</span>
                            </div>
                          </li>
                        <?php } else if ($course['status'] == 'maintenance') { ?>
                          <li class="list-group-item px-0 d-flex justify-content-between">
                            <span>Enrolled Students</span>
                            <span class="fw-semibold"><?php echo $course['enrolled_students']; ?></span>
                          </li>
                          <li class="list-group-item px-0">
                            <div class="alert maintenance-alert mb-0">
                              <i class="bx bx-wrench me-1"></i>
                              <span>Course statistics are limited during maintenance.</span>
                            </div>
                          </li>
                        <?php } else { ?>
                          <li class="list-group-item px-0 d-flex justify-content-between">
                            <span>Enrolled Students</span>
                            <span class="fw-semibold"><?php echo $course['enrolled_students']; ?></span>
                          </li>
                          <li class="list-group-item px-0 d-flex justify-content-between">
                            <span>Completion Rate</span>
                            <span class="fw-semibold"><?php echo $course['completion_rate']; ?>%</span>
                          </li>
                          <?php if ($course['rating'] > 0) { ?>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                              <span>Average Rating</span>
                              <span class="fw-semibold">
                                <?php echo $course['rating']; ?>/5
                                <i class="bx bxs-star text-warning"></i>
                              </span>
                            </li>
                          <?php } ?>
                        <?php } ?>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                          <span>Created On</span>
                          <span class="fw-semibold"><?php echo $course['created_date']; ?></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                          <span>Last Updated</span>
                          <span class="fw-semibold"><?php echo $course['updated_date']; ?></span>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
              <a href="edit-course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">
                <i class="bx bx-edit-alt me-1"></i> Edit Course
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>

    <!-- Analytics Modals -->
    <?php foreach ($courses as $course) { 
      if ($course['status'] != 'draft') { ?>
      <div class="modal fade" id="analyticsModal<?php echo $course['id']; ?>" tabindex="-1" aria-labelledby="analyticsModalLabel<?php echo $course['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="analyticsModalLabel<?php echo $course['id']; ?>">
                Course Analytics: <?php echo htmlspecialchars($course['title']); ?>
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <?php if ($course['status'] == 'maintenance') { ?>
                <div class="alert maintenance-alert mb-4">
                  <div class="d-flex">
                    <i class="bx bx-wrench me-2"></i>
                    <div>
                      <strong>Course in Maintenance Mode</strong>
                      <p class="mb-0">This course is currently being updated. Analytics data may be limited or not reflect recent changes.</p>
                    </div>
                  </div>
                </div>
              <?php } ?>
              
              <!-- Analytics Date Filter -->
              <div class="row mb-4">
                <div class="col-md-6">
                  <div class="d-flex align-items-center">
                    <span class="me-2">Date Range:</span>
                    <select class="form-select form-select-sm">
                      <option value="7days">Last 7 Days</option>
                      <option value="30days">Last 30 Days</option>
                      <option value="90days">Last 90 Days</option>
                      <option value="all" selected>All Time</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6 text-end">
                  <button type="button" class="btn btn-outline-primary btn-sm">
                    <i class="bx bx-download me-1"></i> Download Report
                  </button>
                </div>
              </div>
              
              <!-- Key Metrics Cards -->
              <div class="row mb-4">
                <div class="col-md-3">
                  <div class="card analytics-card h-100">
                    <div class="card-body text-center">
                      <h6 class="card-subtitle mb-1 text-muted">Enrolled Students</h6>
                      <h2 class="card-title mb-2"><?php echo $course['enrolled_students']; ?></h2>
                      <div class="text-success small">
                        <i class="bx bx-trending-up"></i> 12% increase
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card analytics-card h-100">
                    <div class="card-body text-center">
                      <h6 class="card-subtitle mb-1 text-muted">Completion Rate</h6>
                      <h2 class="card-title mb-2"><?php echo $course['completion_rate']; ?>%</h2>
                      <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $course['completion_rate']; ?>%" 
                             aria-valuenow="<?php echo $course['completion_rate']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card analytics-card h-100">
                    <div class="card-body text-center">
                      <h6 class="card-subtitle mb-1 text-muted">Avg. Rating</h6>
                      <h2 class="card-title mb-2"><?php echo $course['rating']; ?>/5</h2>
                      <div class="rating-stars">
                        <?php for ($i = 1; $i <= 5; $i++) { ?>
                          <i class="bx <?php echo $i <= round($course['rating']) ? 'bxs-star' : 'bx-star'; ?>"></i>
                        <?php } ?>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card analytics-card h-100">
                    <div class="card-body text-center">
                      <h6 class="card-subtitle mb-1 text-muted">Revenue Generated</h6>
                      <h2 class="card-title mb-2">$<?php echo number_format($course['enrolled_students'] * 49.99, 2); ?></h2>
                      <div class="text-success small">
                        <i class="bx bx-trending-up"></i> 8% increase
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Charts Row -->
              <div class="row mb-4">
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header d-flex justify-content-between">
                      <h5 class="card-title mb-0">Student Enrollment</h5>
                      <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                          <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                      </div>
                    </div>
                    <div class="card-body">
                      <div style="height: 300px; display: flex; align-items: center; justify-content: center; border: 1px dashed #d9dee3; border-radius: 0.375rem;">
                        <span class="text-muted">Enrollment Trend Chart</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header d-flex justify-content-between">
                      <h5 class="card-title mb-0">Course Engagement</h5>
                      <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                          <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                      </div>
                    </div>
                    <div class="card-body">
                      <div style="height: 300px; display: flex; align-items: center; justify-content: center; border: 1px dashed #d9dee3; border-radius: 0.375rem;">
                        <span class="text-muted">Engagement Metrics Chart</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Popular Content Section -->
              <div class="row">
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h5 class="card-title mb-0">Most Popular Content</h5>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table">
                          <thead>
                            <tr>
                              <th>Content</th>
                              <th>Type</th>
                              <th>Views</th>
                              <th>Completion</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>Introduction to Python</td>
                              <td><span class="badge bg-label-primary">Video</span></td>
                              <td>245</td>
                              <td>92%</td>
                            </tr>
                            <tr>
                              <td>Variables and Data Types</td>
                              <td><span class="badge bg-label-success">Text</span></td>
                              <td>210</td>
                              <td>87%</td>
                            </tr>
                            <tr>
                              <td>Control Flow</td>
                              <td><span class="badge bg-label-primary">Video</span></td>
                              <td>198</td>
                              <td>78%</td>
                            </tr>
                            <tr>
                              <td>Functions</td>
                              <td><span class="badge bg-label-info">Interactive</span></td>
                              <td>183</td>
                              <td>85%</td>
                            </tr>
                            <tr>
                              <td>Lists and Tuples</td>
                              <td><span class="badge bg-label-warning">Quiz</span></td>
                              <td>176</td>
                              <td>92%</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header">
                      <h5 class="card-title mb-0">Latest Reviews</h5>
                    </div>
                    <div class="card-body p-0">
                      <div class="list-group list-group-flush">
                        <div class="list-group-item">
                          <div class="d-flex w-100 justify-content-between mb-1">
                            <h6 class="mb-0">John Doe</h6>
                            <small class="text-muted">3 days ago</small>
                          </div>
                          <div class="mb-1 rating-stars">
                            <i class="bx bxs-star"></i>
                            <i class="bx bxs-star"></i>
                            <i class="bx bxs-star"></i>
                            <i class="bx bxs-star"></i>
                            <i class="bx bxs-star"></i>
                          </div>
                          <p class="mb-0">Great content and well-structured. Very helpful!</p>
                        </div>
                        <div class="list-group-item">
                          <div class="d-flex w-100 justify-content-between mb-1">
                            <h6 class="mb-0">Sarah Johnson</h6>
                            <small class="text-muted">1 week ago</small>
                          </div>
                          <div class="mb-1 rating-stars">
                            <i class="bx bxs-star"></i>
                            <i class="bx bxs-star"></i>
                            <i class="bx bxs-star"></i>
                            <i class="bx bxs-star"></i>
                            <i class="bx bx-star"></i>
                          </div>
                          <p class="mb-0">I learned a lot, but some concepts could be explained better.</p>
                        </div>
                        <div class="list-group-item">
                          <div class="d-flex w-100 justify-content-between mb-1">
                            <h6 class="mb-0">Michael Chen</h6>
                            <small class="text-muted">2 weeks ago</small>
                          </div>
                          <div class="mb-1 rating-stars">
                            <i class="bx bxs-star"></i>
                            <i class="bx bxs-star"></i>
                            <i class="bx bxs-star"></i>
                            <i class="bx bxs-star"></i>
                            <i class="bx bxs-star-half"></i>
                          </div>
                          <p class="mb-0">Excellent course! The practice exercises were particularly helpful.</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <a href="detailed-analytics.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary">
                View Detailed Analytics
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php 
      }
    } 
    ?>

    <!-- Delete Confirmation Modals -->
    <?php foreach ($courses as $course) { ?>
      <div class="modal fade" id="deleteModal<?php echo $course['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $course['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deleteModalLabel<?php echo $course['id']; ?>">Delete Course</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="text-center mb-4">
                <i class="bx bx-trash text-danger" style="font-size: 6rem;"></i>
              </div>
              <p class="text-center">Are you sure you want to delete the course:</p>
              <h4 class="text-center mb-4">"<?php echo htmlspecialchars($course['title']); ?>"</h4>
              <?php if ($course['status'] != 'draft' && $course['enrolled_students'] > 0) { ?>
                <div class="alert alert-warning">
                  <div class="d-flex">
                    <i class="bx bx-error-circle me-2"></i>
                    <div>
                      <p class="mb-0">This course has <strong><?php echo $course['enrolled_students']; ?> enrolled students</strong>. Deleting it will remove their access and progress data.</p>
                      <p class="mb-0 mt-2">Consider <strong>archiving</strong> the course instead to preserve student data.</p>
                    </div>
                  </div>
                </div>
              <?php } ?>
              <p class="text-center text-danger mt-3">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger" onclick="deleteCourse(<?php echo $course['id']; ?>)">Delete Course</button>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
  <!-- / Content -->

  <!-- Maintenance Schedule Modal -->
  <div class="modal fade" id="maintenanceScheduleModal" tabindex="-1" aria-labelledby="maintenanceScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="maintenanceScheduleModalLabel">Schedule Maintenance</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert maintenance-alert mb-4">
            <div class="d-flex align-items-center">
              <i class="bx bx-info-circle me-2"></i>
              <div>
                When a course is set to maintenance mode, students will see a maintenance notice and have limited access to content.
              </div>
            </div>
          </div>
          
          <form id="maintenanceForm">
            <input type="hidden" id="maintenanceCourseId" value="">
            
            <div class="mb-3">
              <label for="maintenanceReason" class="form-label">Maintenance Reason</label>
              <select class="form-select" id="maintenanceReason" required>
                <option value="">Select a reason...</option>
                <option value="content_update">Content Update</option>
                <option value="structure_change">Course Structure Change</option>
                <option value="technical_issues">Technical Issues</option>
                <option value="assessment_revision">Assessment Revision</option>
                <option value="instructor_request">Instructor Request</option>
                <option value="other">Other</option>
              </select>
            </div>
            
            <div class="mb-3" id="otherReasonContainer" style="display: none;">
              <label for="otherReason" class="form-label">Specify Reason</label>
              <input type="text" class="form-control" id="otherReason" placeholder="Please specify the maintenance reason">
            </div>
            
            <div class="mb-3">
              <label for="expectedDuration" class="form-label">Expected Duration</label>
              <select class="form-select" id="expectedDuration" required>
                <option value="">Select duration...</option>
                <option value="1">1 day</option>
                <option value="2">2 days</option>
                <option value="3">3 days</option>
                <option value="5">5 days</option>
                <option value="7">1 week</option>
                <option value="14">2 weeks</option>
                <option value="30">1 month</option>
                <option value="ongoing">Ongoing/Undetermined</option>
              </select>
            </div>
            
            <div class="mb-3">
              <label for="notifyStudents" class="form-label">Notify Students</label>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="notifyStudents" checked>
                <label class="form-check-label" for="notifyStudents">Send email notification to enrolled students</label>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="maintenanceNotes" class="form-label">Maintenance Notes (Internal)</label>
              <textarea class="form-control" id="maintenanceNotes" rows="3" placeholder="Add notes for department staff and instructors"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" onclick="scheduleMaintenanceMode()">Set to Maintenance Mode</button>
        </div>
      </div>
    </div>
  </div>

<?php
// Include the footer
include_once('../includes/admin/footer.php');
?>

<script>
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })
  
  // Pagination variables
  let currentPage = 1;
  const rowsPerPage = 10;
  let filteredRows = [];
  
  // Show toast function
  function showToast(type, message) {
    const toastEl = document.getElementById(type + 'Toast');
    const messageEl = document.getElementById(type + 'ToastMessage');
    
    if (toastEl && messageEl) {
      messageEl.textContent = message;
      const toast = new bootstrap.Toast(toastEl);
      toast.show();
    }
  }

  // Document ready function
  document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-badge');
    const tableRows = document.querySelectorAll('#coursesTable tbody tr');
    const statusCards = document.querySelectorAll('.status-card');
    const searchInput = document.getElementById('courseSearch');
    
    // Store initial rows for pagination
    filteredRows = Array.from(tableRows).filter(row => row.style.display !== 'none');
    updatePagination();
    displayRows(1);
    
    // Status card click filtering
    statusCards.forEach(card => {
      card.addEventListener('click', function() {
        const filter = this.getAttribute('data-filter');
        
        // Update filter button active states
        filterButtons.forEach(btn => {
          btn.classList.remove('active');
          if (btn.getAttribute('data-filter') === filter) {
            btn.classList.add('active');
          }
        });
        
        // Apply filtering
        applyFilter(filter);
      });
    });
    
    // Filter button click handling
    filterButtons.forEach(button => {
      button.addEventListener('click', function() {
        // Remove active class from all buttons
        filterButtons.forEach(btn => btn.classList.remove('active'));
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Get filter value and apply
        const filter = this.getAttribute('data-filter');
        applyFilter(filter);
      });
    });
    
    // Search functionality
    searchInput.addEventListener('keyup', function() {
      const searchTerm = this.value.toLowerCase();
      const activeFilter = document.querySelector('.filter-badge.active').getAttribute('data-filter');
      
      // Filter rows based on search term and active status filter
      filteredRows = Array.from(tableRows).filter(row => {
        const title = row.querySelector('td:first-child').textContent.toLowerCase();
        const rowStatus = row.getAttribute('data-status');
        
        const matchesSearch = title.includes(searchTerm);
        const matchesFilter = activeFilter === 'all' || rowStatus === activeFilter;
        
        return matchesSearch && matchesFilter;
      });
      
      // Reset to first page and update
      currentPage = 1;
      updatePagination();
      displayRows(currentPage);
    });
    
    // Add event listeners for pagination
    document.getElementById('pagination-previous').addEventListener('click', function(e) {
      e.preventDefault();
      if (currentPage > 1) {
        displayRows(currentPage - 1);
      }
    });
    
    document.getElementById('pagination-next').addEventListener('click', function(e) {
      e.preventDefault();
      if (currentPage < Math.ceil(filteredRows.length / rowsPerPage)) {
        displayRows(currentPage + 1);
      }
    });
    
    // Helper function for filtering
    function applyFilter(filter) {
      // Get current search term
      const searchTerm = searchInput.value.toLowerCase();
      
      // Filter table rows
      filteredRows = Array.from(tableRows).filter(row => {
        const title = row.querySelector('td:first-child').textContent.toLowerCase();
        const status = row.getAttribute('data-status');
        
        const matchesSearch = title.includes(searchTerm);
        const matchesFilter = filter === 'all' || status === filter;
        
        return matchesSearch && matchesFilter;
      });
      
      // Reset to first page and update pagination
      currentPage = 1;
      updatePagination();
      displayRows(currentPage);
    }
  });
  
  // Function to update pagination controls
  function updatePagination() {
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    const paginationContainer = document.querySelector('#pagination-container ul');
    const previousButton = document.getElementById('pagination-previous');
    const nextButton = document.getElementById('pagination-next');
    
    // Remove existing page number buttons
    const pageButtons = paginationContainer.querySelectorAll('li:not(#pagination-previous):not(#pagination-next)');
    pageButtons.forEach(button => button.remove());
    
    // Define max visible pages
    const maxPagesToShow = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
    
    // Adjust if we're at the end
    if (endPage - startPage + 1 < maxPagesToShow && startPage > 1) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }
    
    // Add first page + ellipsis if needed
    if (startPage > 1) {
        // Add first page
        addPageButton(1);
        
        // Add ellipsis if there's a gap
        if (startPage > 2) {
            const ellipsis = document.createElement('li');
            ellipsis.className = 'paginate_button page-item disabled';
            ellipsis.innerHTML = '<span class="page-link">...</span>';
            paginationContainer.insertBefore(ellipsis, nextButton);
        }
    }
    
    // Add page buttons in the calculated range
    for (let i = startPage; i <= endPage; i++) {
        addPageButton(i);
    }
    
    // Add ellipsis + last page if needed
    if (endPage < totalPages) {
        // Add ellipsis if there's a gap
        if (endPage < totalPages - 1) {
            const ellipsis = document.createElement('li');
            ellipsis.className = 'paginate_button page-item disabled';
            ellipsis.innerHTML = '<span class="page-link">...</span>';
            paginationContainer.insertBefore(ellipsis, nextButton);
        }
        
        // Add last page
        addPageButton(totalPages);
    }
    
    // Update previous/next button states
    previousButton.classList.toggle('disabled', currentPage === 1);
    nextButton.classList.toggle('disabled', currentPage === totalPages || totalPages === 0);
    
    // Update info text
    updatePaginationInfo();
    
    // Helper function to add a page button
    function addPageButton(pageNum) {
        const pageItem = document.createElement('li');
        pageItem.className = `paginate_button page-item ${pageNum === currentPage ? 'active' : ''}`;
        
        const pageLink = document.createElement('a');
        pageLink.href = '#';
        pageLink.className = 'page-link';
        pageLink.textContent = pageNum;
        
        pageLink.addEventListener('click', function(e) {
            e.preventDefault();
            displayRows(pageNum);
        });
        
        pageItem.appendChild(pageLink);
        paginationContainer.insertBefore(pageItem, nextButton);
    }
  }
  
  // Function to display rows for the current page
  function displayRows(page) {
    currentPage = page;
    const tableRows = document.querySelectorAll('#coursesTable tbody tr');
    
    // Hide all rows first
    tableRows.forEach(row => {
      row.style.display = 'none';
    });
    
    // Show only filtered rows for current page
    const startIndex = (page - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    
    // Show rows for current page
    for (let i = startIndex; i < endIndex && i < filteredRows.length; i++) {
      filteredRows[i].style.display = '';
    }
    
    // Update active page button
    const pageButtons = document.querySelectorAll('#pagination-container .page-item:not(#pagination-previous):not(#pagination-next)');
    pageButtons.forEach((button) => {
      if (button.textContent === page.toString()) {
        button.classList.add('active');
      } else {
        button.classList.remove('active');
      }
    });
    
    // Update previous/next button states
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    document.getElementById('pagination-previous').classList.toggle('disabled', page === 1);
    document.getElementById('pagination-next').classList.toggle('disabled', page === totalPages);
    
    // Update info text
    updatePaginationInfo();
  }
  
  // Update pagination info text
  function updatePaginationInfo() {
    const startIndex = (currentPage - 1) * rowsPerPage + 1;
    const endIndex = Math.min(startIndex + rowsPerPage - 1, filteredRows.length);
    
    document.getElementById('showing-start').textContent = filteredRows.length > 0 ? startIndex : 0;
    document.getElementById('showing-end').textContent = endIndex;
    document.getElementById('total-entries').textContent = filteredRows.length;
  }

  // Function to open course preview modal
  function previewCourse(courseId) {
    const modalId = `previewModal${courseId}`;
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
  }

  // Toggle featured status
  function toggleFeatured(courseId, isFeatured) {
    // Show loading indicator
    showOverlay("Updating featured status...");
    
    setTimeout(() => {
      // Simulate AJAX request to update featured status
      console.log(`Toggling featured status for course ${courseId} to ${isFeatured}`);
      
      // Remove loading indicator after "completion"
      removeOverlay();
      
      // Show success message
      showToast('success', isFeatured ? 
        'Course has been marked as featured and will be highlighted to students.' : 
        'Course has been removed from featured listings.');
      
      // Example AJAX implementation:
      // fetch('../backend/update_featured.php', {
      //   method: 'POST',
      //   headers: { 'Content-Type': 'application/json' },
      //   body: JSON.stringify({ course_id: courseId, is_featured: isFeatured })
      // }).then(response => response.json()).then(data => {
      //   removeOverlay();
      //   if (data.success) {
      //     showToast('success', data.message);
      //   } else {
      //     showToast('error', 'Error updating featured status.');
      //   }
      // }).catch(error => {
      //   removeOverlay();
      //   showToast('error', 'Error updating featured status.');
      // });
    }, 800); // Simulate network delay
  }

  // Update course status
  function updateStatus(courseId, status) {
    // Show loading indicator
    showOverlay("Processing...");
    
    setTimeout(() => {
      // Simulate AJAX request to update status
      console.log(`Updating course ${courseId} status to ${status}`);
      
      // Remove loading indicator after "completion"
      removeOverlay();
      
      // Show success message and reload page
      let message = '';
      
      switch(status) {
        case 'published':
          message = 'Course has been published successfully.';
          break;
        case 'unpublished':
          message = 'Course has been unpublished successfully.';
          break;
        case 'maintenance':
          message = 'Course has been set to maintenance mode. Students will see maintenance notice.';
          break;
        default:
          message = 'Course status has been updated successfully.';
      }
      
      showToast('success', message);
      setTimeout(() => {
        location.reload();
      }, 1500);
      
      // Example AJAX implementation:
      // fetch('../backend/update_course_status.php', {
      //   method: 'POST',
      //   headers: { 'Content-Type': 'application/json' },
      //   body: JSON.stringify({ 
      //     course_id: courseId, 
      //     status: status
      //   })
      // }).then(response => response.json()).then(data => {
      //   removeOverlay();
      //   if (data.success) {
      //     showToast('success', data.message);
      //     setTimeout(() => { location.reload(); }, 1500);
      //   } else {
      //     showToast('error', data.message || 'Error updating course status.');
      //   }
      // }).catch(error => {
      //   removeOverlay();
      //   showToast('error', 'Error updating course status.');
      // });
    }, 1000); // Simulate network delay
  }
  
  // Archive course
  function archiveCourse(courseId) {
    // Show loading indicator
    showOverlay("Archiving course...");
    
    setTimeout(() => {
      // Simulate AJAX request
      console.log(`Archiving course ${courseId}`);
      
      // Remove loading indicator after "completion"
      removeOverlay();
      
      // Show success message and reload page
      showToast('success', 'Course has been archived successfully.');
      setTimeout(() => {
        location.reload();
      }, 1500);
      
      // Example AJAX implementation:
      // fetch('../backend/archive_course.php', {
      //   method: 'POST',
      //   headers: { 'Content-Type': 'application/json' },
      //   body: JSON.stringify({ course_id: courseId })
      // }).then(response => response.json()).then(data => {
      //   removeOverlay();
      //   if (data.success) {
      //     showToast('success', data.message);
      //     setTimeout(() => { location.reload(); }, 1500);
      //   } else {
      //     showToast('error', data.message || 'Error archiving course.');
      //   }
      // }).catch(error => {
      //   removeOverlay();
      //   showToast('error', 'Error archiving course.');
      // });
    }, 1000); // Simulate network delay
  }
  
  // Delete course
  // Open maintenance schedule modal
  function openMaintenanceModal(courseId) {
    // Set the course ID in the hidden field
    document.getElementById('maintenanceCourseId').value = courseId;
    
    // Reset form
    document.getElementById('maintenanceForm').reset();
    document.getElementById('otherReasonContainer').style.display = 'none';
    
    // Show modal
    const maintenanceModal = new bootstrap.Modal(document.getElementById('maintenanceScheduleModal'));
    maintenanceModal.show();
  }
  
  // Schedule maintenance mode
  function scheduleMaintenanceMode() {
    const courseId = document.getElementById('maintenanceCourseId').value;
    const reason = document.getElementById('maintenanceReason').value;
    const duration = document.getElementById('expectedDuration').value;
    const notifyStudents = document.getElementById('notifyStudents').checked;
    const notes = document.getElementById('maintenanceNotes').value;
    let maintenanceReason = reason;
    
    // Check if "Other" reason is selected
    if (reason === 'other') {
      maintenanceReason = document.getElementById('otherReason').value;
      if (!maintenanceReason) {
        showToast('error', 'Please specify the maintenance reason.');
        return;
      }
    }
    
    // Validate form
    if (!reason || !duration) {
      showToast('error', 'Please fill in all required fields.');
      return;
    }
    
    // Show loading indicator
    showOverlay("Setting course to maintenance mode...");
    
    // Close modal
    const maintenanceModal = bootstrap.Modal.getInstance(document.getElementById('maintenanceScheduleModal'));
    maintenanceModal.hide();
    
    setTimeout(() => {
      // Simulate AJAX request
      console.log(`Setting course ${courseId} to maintenance mode`);
      console.log(`Reason: ${maintenanceReason}, Duration: ${duration} days, Notify Students: ${notifyStudents}, Notes: ${notes}`);
      
      // Remove loading indicator after "completion"
      removeOverlay();
      
      // Show success message and reload page
      showToast('success', 'Course has been set to maintenance mode successfully.');
      setTimeout(() => {
        location.reload();
      }, 1500);
      
      // Example AJAX implementation:
      // fetch('../backend/schedule_maintenance.php', {
      //   method: 'POST',
      //   headers: { 'Content-Type': 'application/json' },
      //   body: JSON.stringify({ 
      //     course_id: courseId,
      //     reason: maintenanceReason,
      //     duration: duration,
      //     notify_students: notifyStudents,
      //     notes: notes
      //   })
      // }).then(response => response.json()).then(data => {
      //   removeOverlay();
      //   if (data.success) {
      //     showToast('success', data.message);
      //     setTimeout(() => { location.reload(); }, 1500);
      //   } else {
      //     showToast('error', data.message || 'Error scheduling maintenance.');
      //   }
      // }).catch(error => {
      //   removeOverlay();
      //   showToast('error', 'Error scheduling maintenance.');
      // });
    }, 1500); // Simulate network delay
  }
  
  // Handle reason dropdown change
  document.addEventListener('DOMContentLoaded', function() {
    const reasonDropdown = document.getElementById('maintenanceReason');
    if (reasonDropdown) {
      reasonDropdown.addEventListener('change', function() {
        const otherReasonContainer = document.getElementById('otherReasonContainer');
        if (this.value === 'other') {
          otherReasonContainer.style.display = 'block';
        } else {
          otherReasonContainer.style.display = 'none';
        }
      });
    }
  });
  
  function deleteCourse(courseId) {
    // Show loading indicator
    showOverlay("Deleting course...");
    
    setTimeout(() => {
      // Simulate AJAX request
      console.log(`Deleting course ${courseId}`);
      
      // Remove loading indicator after "completion"
      removeOverlay();
      
      // Close modal
      const modalId = `deleteModal${courseId}`;
      const modal = document.getElementById(modalId);
      if (modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
      }
      
      // Show success message and reload page
      showToast('success', 'Course has been deleted successfully.');
      setTimeout(() => {
        location.reload();
      }, 1500);
      
      // Example AJAX implementation:
      // fetch('../backend/delete_course.php', {
      //   method: 'POST',
      //   headers: { 'Content-Type': 'application/json' },
      //   body: JSON.stringify({ course_id: courseId })
      // }).then(response => response.json()).then(data => {
      //   removeOverlay();
      //   if (data.success) {
      //     const modalId = `deleteModal${courseId}`;
      //     bootstrap.Modal.getInstance(document.getElementById(modalId)).hide();
      //     showToast('success', data.message);
      //     setTimeout(() => { location.reload(); }, 1500);
      //   } else {
      //     showToast('error', data.message || 'Error deleting course.');
      //   }
      // }).catch(error => {
      //   removeOverlay();
      //   showToast('error', 'Error deleting course.');
      // });
    }, 1200); // Simulate network delay
  }
</script>