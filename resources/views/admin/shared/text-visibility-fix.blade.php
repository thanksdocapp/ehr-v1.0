<style>
/* Global text visibility fixes for admin views */

/* Base text colors */
body, 
.container-fluid, 
.row, 
.col, 
[class*="col-"] {
    color: #495057 !important;
}

/* Page titles and subtitles */
.page-title h1, 
.page-title h2, 
.page-title h3, 
.page-title h4, 
.page-title h5, 
.page-title h6 {
    color: #2c3e50 !important;
}

.page-subtitle {
    color: #6c757d !important;
}

/* Form elements */
.form-label, 
label {
    color: #495057 !important;
    font-weight: 600;
}

.form-control, 
.form-select, 
select, 
input, 
textarea {
    color: #495057 !important;
    background-color: #fff !important;
    border: 1px solid #ced4da !important;
}

.form-control:focus, 
.form-select:focus {
    color: #495057 !important;
    background-color: #fff !important;
    border-color: #80bdff !important;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
}

.form-help {
    color: #6c757d !important;
    font-size: 0.875rem;
}

/* Card components */
.card-body, 
.admin-card .card-body {
    color: #495057 !important;
}

.card-header h5, 
.card-title {
    color: inherit !important;
}

/* Admin cards with gradient headers */
.admin-card .card-header {
    color: white !important;
}

.admin-card .card-header h1, 
.admin-card .card-header h2, 
.admin-card .card-header h3, 
.admin-card .card-header h4, 
.admin-card .card-header h5, 
.admin-card .card-header h6 {
    color: white !important;
}

.admin-card .card-header small {
    color: rgba(255, 255, 255, 0.8) !important;
}

/* Form sections with clean headers */
.form-section-header {
    background: #f8f9fc !important;
    color: #2d3748 !important;
    border-bottom: 2px solid #e2e8f0 !important;
}

.form-section-header h1, 
.form-section-header h2, 
.form-section-header h3, 
.form-section-header h4, 
.form-section-header h5, 
.form-section-header h6 {
    color: #1a202c !important;
    font-weight: 700 !important;
}

.form-section-header i {
    color: #1a202c !important;
}

.form-section-header small {
    color: #4a5568 !important;
}

.form-section-body {
    color: #495057 !important;
}

.form-section-body p, 
.form-section-body div, 
.form-section-body span, 
.form-section-body label {
    color: #495057 !important;
}

/* Information items */
.info-item label {
    color: #6c757d !important;
    font-size: 0.85rem;
    font-weight: 600;
}

.info-value {
    color: #495057 !important;
    font-size: 0.95rem;
    font-weight: 500;
}

/* Timeline components */
.timeline-content h6 {
    color: #495057 !important;
}

.timeline-content p, 
.timeline-content small {
    color: #6c757d !important;
}

/* User info sections */
.user-info h6 {
    color: #2c3e50 !important;
}

/* Table content */
.table td, 
.table th {
    color: #495057 !important;
}

.table thead th {
    color: #495057 !important;
    font-weight: 600;
}

/* Text utility classes override */
.text-muted {
    color: #6c757d !important;
}

.text-success {
    color: #28a745 !important;
}

.text-primary {
    color: #007bff !important;
}

.text-info {
    color: #17a2b8 !important;
}

.text-secondary {
    color: #6c757d !important;
}

.text-warning {
    color: #ffc107 !important;
}

.text-danger {
    color: #dc3545 !important;
}

/* Alert components */
.alert {
    color: inherit !important;
}

.alert-success {
    color: #155724 !important;
    background-color: #d4edda !important;
    border-color: #c3e6cb !important;
}

.alert-danger {
    color: #721c24 !important;
    background-color: #f8d7da !important;
    border-color: #f5c6cb !important;
}

.alert-warning {
    color: #856404 !important;
    background-color: #fff3cd !important;
    border-color: #ffeaa7 !important;
}

.alert-info {
    color: #0c5460 !important;
    background-color: #d1ecf1 !important;
    border-color: #b8daff !important;
}

/* Modal components */
.modal-body {
    color: #495057 !important;
}

.modal-body p, 
.modal-body div, 
.modal-body span, 
.modal-body label {
    color: #495057 !important;
}

.modal-header {
    color: #495057 !important;
}

.modal-title {
    color: #495057 !important;
}

/* Breadcrumb */
.breadcrumb-item a {
    color: #007bff !important;
}

.breadcrumb-item.active {
    color: #6c757d !important;
}

/* Button text */
.btn {
    color: inherit !important;
}

/* Badge text */
.badge {
    color: inherit !important;
}

/* Ensure specific admin elements are visible */
.admin-card p, 
.admin-card div:not(.card-header), 
.admin-card span {
    color: #495057 !important;
}

/* Fix any dark/invisible text */
.admin-card .card-body .text-dark {
    color: #495057 !important;
}

/* Dropdown menus */
.dropdown-menu {
    color: #495057 !important;
}

.dropdown-item {
    color: #495057 !important;
}

.dropdown-item:hover {
    color: #16181b !important;
    background-color: #f8f9fa !important;
}

/* Navigation elements */
.nav-link {
    color: #495057 !important;
}

/* List groups */
.list-group-item {
    color: #495057 !important;
}

/* Code elements */
code {
    color: #e83e8c !important;
    background-color: #f8f9fa !important;
}

/* Small text elements */
small {
    color: #6c757d !important;
}

/* Strong/bold text */
strong, b {
    color: #495057 !important;
    font-weight: 600;
}

/* Ensure no text is invisible */
* {
    color: inherit;
}

/* Override any theme that might be making text invisible */
.container-fluid *, 
.admin-card *, 
.form-section * {
    color: inherit !important;
}

/* Special case for gradient card content that should stay white */
[style*="background: linear-gradient"] *, 
[style*="background:linear-gradient"] * {
    color: white !important;
}

/* But ensure form inputs in gradient backgrounds are still readable */
[style*="background: linear-gradient"] .form-control, 
[style*="background:linear-gradient"] .form-control {
    color: #495057 !important;
    background-color: #fff !important;
}
</style>
