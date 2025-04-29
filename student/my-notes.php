<?php
// Include header
include '../includes/student-header.php';
?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main" class="bg-light">
  <!-- Breadcrumb -->
  <?php include '../includes/student-breadcrumb.php'; ?>
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
                        src="../uploads/profile/<?php echo $row['profile_pic'] ?>"
                        alt="Profile">
                    </div>
                  </div>
                  <h4 class="card-title mb-0"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></h4>
                  <p class="card-text small"><?php echo $row['email']; ?></p>
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
                    <a class="nav-link active" href="report.php">
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
          <div id="notesCard" class="card">
            <div class="card-header border-bottom">
              <h4 class="card-header-title">My Notes</h4>
            </div>
            
  <?php


// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
  header('Content-Type: application/json');

  // Save new note
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_note') {
    $course_id = intval($_POST['course_id']);
    $topic_id = intval($_POST['topic_id']);
    $content = trim($_POST['content']);

    if ($course_id <= 0 || $topic_id <= 0 || empty($content)) {
      echo json_encode(['success' => false, 'message' => 'Invalid course, topic, or content']);
      exit();
    }

    // Verify enrollment
    $check_query = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'Active'";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
      echo json_encode(['success' => false, 'message' => 'Not enrolled in this course']);
      exit();
    }

    // Save note
    $insert_query = "INSERT INTO student_notes (user_id, topic_id, content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iis", $user_id, $topic_id, $content);
    $success = $stmt->execute();

    if ($success) {
      $note_id = $stmt->insert_id;
      echo json_encode(['success' => true, 'note_id' => $note_id]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to save note']);
    }
    exit();
  }

  // Delete note
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_note') {
    $note_id = intval($_POST['note_id']);

    $check_query = "SELECT * FROM student_notes WHERE note_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $note_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
      echo json_encode(['success' => false, 'message' => 'Note not found or access denied']);
      exit();
    }

    $delete_query = "DELETE FROM student_notes WHERE note_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $note_id);
    $success = $stmt->execute();

    echo json_encode(['success' => $success]);
    exit();
  }
}

// Fetch all courses and topics the student is enrolled in
$courses_query = "SELECT c.course_id, c.title, c.thumbnail, st.topic_id, st.title as topic_title
               FROM courses c 
               JOIN enrollments e ON c.course_id = e.course_id 
               JOIN course_sections cs ON c.course_id = cs.course_id
               JOIN section_topics st ON cs.section_id = st.section_id
               WHERE e.user_id = ? AND e.status = 'Active'
               ORDER BY c.title ASC, st.title ASC";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses_result = $stmt->get_result();

$courses = [];
$topics = [];
while ($row = $courses_result->fetch_assoc()) {
  $courses[$row['course_id']] = [
    'title' => $row['title'],
    'thumbnail' => $row['thumbnail']
  ];
  $topics[$row['course_id']][] = [
    'topic_id' => $row['topic_id'],
    'title' => $row['topic_title']
  ];
}

// Fetch notes
$notes_query = "SELECT 
              sn.note_id, 
              sn.content, 
              sn.created_at,
              sn.updated_at,
              st.topic_id,
              st.title as topic_title,
              cs.section_id,
              cs.title as section_title,
              c.course_id,
              c.title as course_title,
              c.thumbnail as course_thumbnail
              FROM student_notes sn
              JOIN section_topics st ON sn.topic_id = st.topic_id
              JOIN course_sections cs ON st.section_id = cs.section_id
              JOIN courses c ON cs.course_id = c.course_id
              JOIN enrollments e ON c.course_id = e.course_id AND e.user_id = ?
              WHERE sn.user_id = ?
              ORDER BY sn.updated_at DESC";
$stmt = $conn->prepare($notes_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$notes_result = $stmt->get_result();

$notes = [];
while ($note = $notes_result->fetch_assoc()) {
  $notes[] = [
    'id' => $note['note_id'],
    'course_id' => $note['course_id'],
    'course' => $note['course_title'],
    'section' => $note['section_title'],
    'topic_id' => $note['topic_id'],
    'topic' => $note['topic_title'],
    'thumbnail' => $note['course_thumbnail'],
    'content' => $note['content'],
    'created_at' => $note['created_at'],
    'updated_at' => $note['updated_at']
  ];
}

// Function to count words
function countWords($string)
{
  return str_word_count(strip_tags($string));
}

// Function to get summary
function getSummary($text, $maxLength = 150)
{
  $text = strip_tags($text);
  if (strlen($text) > $maxLength) {
    $text = substr($text, 0, $maxLength) . '...';
  }
  return $text;
}
?>


            <!-- Body -->
            <div class="card-body">
              <!-- Note Creation Form -->
              <div class="mb-6">
                <h5 class="mb-3">Add New Note</h5>
                <div class="flex flex-col space-y-4">
                  <select class="form-select" id="noteCourse" required>
                    <option value="" disabled selected>Select Course</option>
                    <?php foreach ($courses as $course_id => $course): ?>
                      <option value="<?php echo $course_id; ?>" data-topics='<?php echo json_encode($topics[$course_id]); ?>'><?php echo htmlspecialchars($course['title']); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <select class="form-select" id="noteTopic" required>
                    <option value="" disabled selected>Select Topic</option>
                  </select>
                  <textarea class="form-control" id="noteContent" rows="4" placeholder="Write your note here..." required></textarea>
                  <button class="btn btn-primary w-32 self-end" onclick="addNote()">Save Note</button>
                </div>
              </div>

              <!-- Note Controls -->
              <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="flex space-x-4 w-full md:w-auto">
                  <div class="relative w-full md:w-64">
                    <input type="text" class="form-control pl-10" id="searchNotes" placeholder="Search notes...">
                    <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                  </div>
                  <select class="form-select w-full md:w-40" id="filterCourse">
                    <option value="all">All Courses</option>
                    <?php foreach ($courses as $course_id => $course): ?>
                      <option value="<?php echo $course_id; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">Reset</button>
                </div>
              </div>

              <!-- Note List -->
              <div id="noteList" class="space-y-4">
                <?php foreach ($notes as $note): ?>
                  <div class="note-item p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow"
                    data-course-id="<?php echo $note['course_id']; ?>"
                    data-id="<?php echo $note['id']; ?>">
                    <div class="flex justify-between items-center">
                      <div class="flex items-start space-x-4">
                        <img src="../Uploads/thumbnails/<?php echo htmlspecialchars($note['thumbnail'] ?: 'default-course.jpg'); ?>"
                          alt="<?php echo htmlspecialchars($note['course']); ?>"
                          class="w-16 h-16 object-cover rounded"
                          loading="lazy">
                        <div>
                          <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                              <li class="breadcrumb-item"><?php echo htmlspecialchars($note['course']); ?></li>
                              <li class="breadcrumb-item"><?php echo htmlspecialchars($note['section']); ?></li>
                              <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($note['topic']); ?></li>
                            </ol>
                          </nav>
                          <p class="text-sm text-gray-600"><?php echo htmlspecialchars(getSummary($note['content'])); ?></p>
                          <span class="text-xs text-gray-400">Updated: <?php echo date('M j, Y', strtotime($note['updated_at'])); ?></span>
                        </div>
                      </div>
                      <div class="flex space-x-2">
                        <button class="btn btn-sm btn-outline-secondary"
                          onclick="viewNoteDetails(this)"
                          data-id="<?php echo $note['id']; ?>">
                          <i class="bi bi-eye"></i>
                        </button>
                        <a href="course-content.php?course_id=<?php echo $note['course_id']; ?>&topic=<?php echo $note['topic_id']; ?>#notes"
                          class="btn btn-sm btn-soft-primary">Edit</a>
                        <button class="btn btn-sm btn-soft-danger"
                          onclick="deleteNote(this)"
                          data-id="<?php echo $note['id']; ?>">Delete</button>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <!-- Empty State -->
              <div id="emptyNotes" class="text-center py-8 <?php echo count($notes) > 0 ? 'hidden' : ''; ?>">
                <i class="bi bi-sticky text-gray-400 text-4xl mb-2"></i>
                <p class="text-gray-600">No notes created yet. Add a note to organize your thoughts!</p>
                <a href="courses.php" class="btn btn-primary mt-3">Go to My Courses</a>
              </div>
            </div>
            <!-- End Body -->
          </div>
          <!-- End Card -->

          <!-- Note Details Modal -->
          <div class="modal fade" id="noteDetailsModal" tabindex="-1" aria-labelledby="noteDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content rounded-lg shadow-lg">
                <div class="modal-header border-0 bg-gradient-primary text-white p-4">
                  <div class="flex items-center">
                    <i class="bi bi-journal-text me-2 text-2xl"></i>
                    <h5 class="modal-title font-bold text-lg" id="noteDetailsModalLabel">Note Details</h5>
                  </div>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-6 bg-white">
                  <!-- Breadcrumb Navigation -->
                  <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><span id="modalCourseCrumb"></span></li>
                      <li class="breadcrumb-item"><span id="modalSectionCrumb"></span></li>
                      <li class="breadcrumb-item active" aria-current="page"><span id="modalTopicCrumb"></span></li>
                    </ol>
                  </nav>
                  <div class="relative">
                    <img id="modalThumbnail" src="../assets/images/course-placeholder.jpg" alt="Course Thumbnail"
                      class="w-full h-48 object-cover rounded-lg mb-4" loading="lazy">
                  </div>
                  <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <p id="modalContent" class="text-base text-gray-800 leading-relaxed" style="white-space: pre-wrap; max-height: 300px; overflow-y: auto;"></p>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                      <p class="text-sm font-medium text-gray-700">Course</p>
                      <p id="modalCourse" class="text-sm text-gray-600"></p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-700">Section</p>
                      <p id="modalSection" class="text-sm text-gray-600"></p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-700">Topic</p>
                      <p id="modalTopic" class="text-sm text-gray-600"></p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-700">Word Count</p>
                      <p id="modalWordCount" class="text-sm text-gray-600"></p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-700">Created</p>
                      <p id="modalCreated" class="text-sm text-gray-600"></p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-700">Updated</p>
                      <p id="modalUpdated" class="text-sm text-gray-600"></p>
                    </div>
                  </div>
                </div>
                <div class="modal-footer border-0">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                  <a id="modalEditLink" href="#" class="btn btn-primary">Edit Note</a>
                  <button id="modalDeleteNoteBtn" class="btn btn-danger">Delete</button>
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
        </div>
      </div>
      <!-- End Col -->
    </div>
    <!-- End Row -->
  </div>
  <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->

<!-- ========== FOOTER ========== -->
<?php include '../includes/student-footer.php'; ?>
<!-- ========== END FOOTER ========== -->

<!-- Tailwind CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<!-- Bootstrap JS for Modal -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
  .bg-gradient-primary {
    background: linear-gradient(135deg, #6b7280 0%, #3b82f6 100%);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .hover-shadow {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
  }

  .modal {
    transition: opacity 0.3s ease, transform 0.3s ease;
  }

  .modal.fade .modal-dialog {
    transform: scale(0.8);
  }

  .modal.show .modal-dialog {
    transform: scale(1);
  }

  .modal-content {
    z-index: 1055;
  }
</style>

<script>
  // Note data from PHP
  let notes = <?php echo json_encode($notes); ?>;
  let courses = <?php echo json_encode($courses); ?>;
  let topics = <?php echo json_encode($topics); ?>;

  // Debounce function
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // Render notes
  function renderNotes(filteredNotes) {
    const noteList = document.getElementById('noteList');
    const emptyNotes = document.getElementById('emptyNotes');
    const fragment = document.createDocumentFragment();

    if (filteredNotes.length === 0) {
      if (emptyNotes) emptyNotes.classList.remove('hidden');
      noteList.classList.add('hidden');
      return;
    }

    if (emptyNotes) emptyNotes.classList.add('hidden');
    noteList.classList.remove('hidden');
    noteList.innerHTML = '';

    filteredNotes.forEach(note => {
      const noteItem = document.createElement('div');
      noteItem.className = 'note-item p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow';
      noteItem.dataset.courseId = note.course_id;
      noteItem.dataset.id = note.id;
      noteItem.innerHTML = `
        <div class="flex justify-between items-center">
          <div class="flex items-start space-x-4">
            <img src="../Uploads/thumbnails/${note.thumbnail || 'default-course.jpg'}" 
                 alt="${note.course}" 
                 class="w-16 h-16 object-cover rounded" 
                 loading="lazy">
            <div>
              <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">${note.course}</li>
    <li class="breadcrumb-item">${note.section}</li>
    <li class="breadcrumb-item active" aria-current="page">${note.topic}</li>
  </ol>
</nav>
              <p class="text-sm text-gray-600">${note.content.length > 150 ? note.content.substring(0, 150) + '...' : note.content}</p>
              <span class="text-xs text-gray-400">Updated: ${new Date(note.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
            </div>
          </div>
          <div class="flex space-x-2">
            <button class="btn btn-sm btn-outline-secondary" 
                    onclick="viewNoteDetails(this)" 
                    data-id="${note.id}">
              <i class="bi bi-eye"></i>
            </button>
            <a href="course-content.php?course_id=${note.course_id}&topic=${note.topic_id}#notes" 
               class="btn btn-sm btn-soft-primary">Edit</a>
            <button class="btn btn-sm btn-soft-danger" 
                    onclick="deleteNote(this)" 
                    data-id="${note.id}">Delete</button>
          </div>
        </div>
      `;
      fragment.appendChild(noteItem);
    });

    noteList.appendChild(fragment);
  }

  // Filter notes
  function filterNotes() {
    const searchQuery = document.getElementById('searchNotes').value.toLowerCase();
    const filterCourse = document.getElementById('filterCourse').value;

    let filteredNotes = notes;
    if (filterCourse !== 'all') {
      filteredNotes = notes.filter(n => n.course_id.toString() === filterCourse);
    }
    if (searchQuery) {
      filteredNotes = filteredNotes.filter(n =>
        n.content.toLowerCase().includes(searchQuery) ||
        n.course.toLowerCase().includes(searchQuery) ||
        n.section.toLowerCase().includes(searchQuery) ||
        n.topic.toLowerCase().includes(searchQuery)
      );
    }

    renderNotes(filteredNotes);
  }

  // Debounced filter
  const debouncedFilterNotes = debounce(filterNotes, 300);

  // Reset filters
  function resetFilters() {
    const searchNotes = document.getElementById('searchNotes');
    const filterCourse = document.getElementById('filterCourse');
    if (searchNotes) searchNotes.value = '';
    if (filterCourse) filterCourse.value = 'all';
    renderNotes(notes);
  }

  // Add new note
  function addNote() {
    const courseSelect = document.getElementById('noteCourse');
    const topicSelect = document.getElementById('noteTopic');
    const content = document.getElementById('noteContent').value.trim();
    const course_id = parseInt(courseSelect.value);
    const topic_id = parseInt(topicSelect.value);

    if (!course_id || !topic_id || !content) {
      showToast('Error', 'Please select a course, topic, and enter note content.');
      return;
    }

    fetch('my-notes.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: `action=save_note&course_id=${course_id}&topic_id=${topic_id}&content=${encodeURIComponent(content)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const newNote = {
            id: data.note_id,
            course_id: course_id,
            course: courseSelect.options[courseSelect.selectedIndex].text,
            section: topics[course_id].find(t => t.topic_id === topic_id)?.section || 'Unknown Section',
            topic_id: topic_id,
            topic: topicSelect.options[topicSelect.selectedIndex].text,
            thumbnail: courses[course_id].thumbnail || 'default-course.jpg',
            content: content,
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString()
          };
          notes.unshift(newNote);
          courseSelect.value = '';
          topicSelect.value = '';
          document.getElementById('noteContent').value = '';
          renderNotes(notes);
          showToast('Success', 'Note saved successfully!');
        } else {
          showToast('Error', data.message || 'Failed to save note');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'An error occurred while saving the note');
      });
  }

  // View note details in modal
  function viewNoteDetails(button) {
    const id = parseInt(button.dataset.id);
    const note = notes.find(n => n.id === id);
    if (!note) return;

    const modalThumbnail = document.getElementById('modalThumbnail');
    const modalContent = document.getElementById('modalContent');
    const modalCourse = document.getElementById('modalCourse');
    const modalSection = document.getElementById('modalSection');
    const modalTopic = document.getElementById('modalTopic');
    const modalCourseCrumb = document.getElementById('modalCourseCrumb');
    const modalSectionCrumb = document.getElementById('modalSectionCrumb');
    const modalTopicCrumb = document.getElementById('modalTopicCrumb');
    const modalWordCount = document.getElementById('modalWordCount');
    const modalCreated = document.getElementById('modalCreated');
    const modalUpdated = document.getElementById('modalUpdated');
    const modalEditLink = document.getElementById('modalEditLink');
    const modalDeleteBtn = document.getElementById('modalDeleteNoteBtn');

    if (modalThumbnail) modalThumbnail.src = `../Uploads/thumbnails/${note.thumbnail || 'default-course.jpg'}`;
    if (modalContent) modalContent.textContent = note.content;
    if (modalCourse) modalCourse.textContent = note.course;
    if (modalSection) modalSection.textContent = note.section;
    if (modalTopic) modalTopic.textContent = note.topic;
    if (modalCourseCrumb) modalCourseCrumb.textContent = note.course;
    if (modalSectionCrumb) modalSectionCrumb.textContent = note.section;
    if (modalTopicCrumb) modalTopicCrumb.textContent = note.topic;
    if (modalWordCount) modalWordCount.textContent = countWords(note.content);
    if (modalCreated) modalCreated.textContent = new Date(note.created_at).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
    if (modalUpdated) modalUpdated.textContent = new Date(note.updated_at).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
    if (modalEditLink) modalEditLink.href = `course-content.php?course_id=${note.course_id}&topic=${note.topic_id}#notes`;
    if (modalDeleteBtn) modalDeleteBtn.dataset.id = note.id;

    const modal = new bootstrap.Modal(document.getElementById('noteDetailsModal'));
    modal.show();
  }

  // Delete note
  function deleteNote(button) {
    const id = parseInt(button.dataset.id);
    const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    confirmDeleteBtn.onclick = function() {
      fetch('my-notes.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: `action=delete_note¬e_id=${id}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            notes = notes.filter(n => n.id !== id);
            renderNotes(notes);
            deleteConfirmModal.hide();
            const viewModal = bootstrap.Modal.getInstance(document.getElementById('noteDetailsModal'));
            if (viewModal) viewModal.hide();
            showToast('Success', 'Note deleted successfully!');
          } else {
            showToast('Error', data.message || 'Failed to delete note');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Error', 'An error occurred while deleting the note');
        });
    };

    deleteConfirmModal.show();
  }

  // Show toast notification
  function showToast(title, message) {
    const toastContainer = document.createElement('div');
    toastContainer.className = 'position-fixed top-0 end-0 p-3';
    toastContainer.style.zIndex = '1080';
    toastContainer.innerHTML = `
      <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">${title}</strong>
          <small>Just now</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">${message}</div>
      </div>
    `;
    document.body.appendChild(toastContainer);

    const toastElement = toastContainer.querySelector('.toast');
    const toast = new bootstrap.Toast(toastElement, {
      delay: 3000
    });
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', () => {
      document.body.removeChild(toastContainer);
    });
  }

  // Function to count words (client-side)
  function countWords(str) {
    return str.trim().split(/\s+/).length;
  }

  // Populate topics based on course selection
  document.getElementById('noteCourse').addEventListener('change', function() {
    const courseId = this.value;
    const topicSelect = document.getElementById('noteTopic');
    topicSelect.innerHTML = '<option value="" disabled selected>Select Topic</option>';

    if (courseId && topics[courseId]) {
      topics[courseId].forEach(topic => {
        const option = document.createElement('option');
        option.value = topic.topic_id;
        option.textContent = topic.title;
        topicSelect.appendChild(option);
      });
    }
  });

  // Attach debounced event listener
  document.getElementById('searchNotes').addEventListener('input', debouncedFilterNotes);
  document.getElementById('filterCourse').addEventListener('change', filterNotes);

  // Modal delete button
  document.getElementById('modalDeleteNoteBtn').addEventListener('click', function() {
    const id = parseInt(this.dataset.id);
    const button = document.querySelector(`.note-item[data-id="${id}"] .btn-soft-danger`);
    if (button) deleteNote(button);
  });

  // Initial render
  renderNotes(notes);
</script>