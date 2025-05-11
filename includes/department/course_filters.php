<?php
// includes/department/course_filters.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function renderFilterBar($categories = [], $levels = []) {
    ?>
    <div class="card mb-3">
        <div class="card-header border-0 py-2">
            <div class="row justify-content-between align-items-center">
                <div class="col-auto">
                    <h5 class="card-header-title mb-0">Course Management</h5>
                </div>
                <div class="col-auto">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary active" data-view="cards">
                            <i class="bi-grid-3x3-gap"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-view="table">
                            <i class="bi-list-ul"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body py-2">
            <div class="row align-items-center g-2">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">
                            <i class="bi-search"></i>
                        </span>
                        <input type="text" id="courseSearch" class="form-control" placeholder="Search courses..." autocomplete="off">
                    </div>
                </div>
                
                <div class="col-md-9">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="published">Published</option>
                                <option value="review">Under Review</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" id="categoryFilter">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['name']); ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" id="levelFilter">
                                <option value="">All Levels</option>
                                <?php foreach ($levels as $level): ?>
                                    <option value="<?php echo htmlspecialchars($level); ?>">
                                        <?php echo htmlspecialchars($level); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" id="sortFilter">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="name">Name A-Z</option>
                                <option value="updated">Recently Updated</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function renderQuickFilters() {
    ?>
    <div class="nav nav-segment nav-sm mb-3" id="quickFilters">
        <a class="nav-item nav-link active" href="#" data-filter="">All Courses</a>
        <a class="nav-item nav-link" href="#" data-filter="status=draft">Drafts</a>
        <a class="nav-item nav-link" href="#" data-filter="status=review">Under Review</a>
        <a class="nav-item nav-link" href="#" data-filter="status=published">Published</a>
        <a class="nav-item nav-link" href="#" data-filter="needs_attention">Needs Attention</a>
    </div>
    <?php
}

function renderBulkActions() {
    ?>
    <div class="d-flex align-items-center gap-2 mb-3" id="bulkActionsBar" style="display: none !important;">
        <button type="button" class="btn btn-sm btn-ghost-primary" id="selectAllCourses">
            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
            <span class="ms-2">Select All</span>
        </button>
        
        <div class="vr"></div>
        
        <div class="dropdown">
            <button type="button" class="btn btn-sm btn-white dropdown-toggle" data-bs-toggle="dropdown">
                Bulk Actions
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#" data-bulk-action="archive">
                    <i class="bi-archive me-2"></i> Archive Selected
                </a>
                <a class="dropdown-item" href="#" data-bulk-action="submit_review">
                    <i class="bi-send me-2"></i> Submit for Review
                </a>
                <a class="dropdown-item" href="#" data-bulk-action="publish">
                    <i class="bi-eye me-2"></i> Publish Selected
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="#" data-bulk-action="delete">
                    <i class="bi-trash me-2"></i> Delete Selected
                </a>
            </div>
        </div>
        
        <span class="text-muted small" id="selectedCount">0 selected</span>
    </div>
    <?php
}
?>