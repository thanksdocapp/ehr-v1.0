<style>
    /* ============================================
       MODERN UI SYSTEM FOR ADMIN PAGES
       ============================================ */

    :root {
        --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --gradient-success: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        --gradient-info: linear-gradient(135deg, #3494E6 0%, #EC6EAD 100%);
        --gradient-warning: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --gradient-danger: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
        
        --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.08);
        --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.12);
        --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.15);
        
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --radius-xl: 20px;
    }

    /* ============================================
       MODERN PAGE HEADER
       ============================================ */
    .modern-page-header {
        background: var(--gradient-primary);
        border-radius: var(--radius-xl);
        padding: 2rem 2.5rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
        position: relative;
        overflow: hidden;
    }

    .modern-page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .modern-page-header-content {
        position: relative;
        z-index: 1;
    }

    .modern-page-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .modern-page-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 0;
    }

    /* ============================================
       MODERN CARDS
       ============================================ */
    .modern-card {
        background: white;
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        box-shadow: var(--shadow-sm);
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin-bottom: 1.5rem;
    }

    .modern-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .modern-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #f7fafc;
    }

    .modern-card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .modern-card-title i {
        color: #667eea;
    }

    .modern-card-body {
        padding: 0;
    }

    /* ============================================
       MODERN TABLES
       ============================================ */
    .modern-table-wrapper {
        overflow-x: auto;
        border-radius: var(--radius-md);
    }

    .modern-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .modern-table thead {
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    }

    .modern-table thead th {
        padding: 1.25rem 1rem;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #4a5568;
        border: none;
        border-bottom: 2px solid #e2e8f0;
    }

    .modern-table thead th:first-child {
        border-top-left-radius: var(--radius-md);
    }

    .modern-table thead th:last-child {
        border-top-right-radius: var(--radius-md);
    }

    .modern-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .modern-table tbody tr:hover {
        background: #f8fafc;
        transform: translateX(4px);
    }

    .modern-table tbody tr:last-child {
        border-bottom: none;
    }

    .modern-table tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
        border: none;
        color: #2d3748;
    }

    /* ============================================
       MODERN FORMS
       ============================================ */
    .modern-form-group {
        margin-bottom: 1.5rem;
    }

    .modern-form-label {
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
        display: block;
    }

    .modern-form-control {
        width: 100%;
        padding: 0.875rem 1.25rem;
        border: 2px solid #e2e8f0;
        border-radius: var(--radius-md);
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: white;
    }

    .modern-form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .modern-form-select {
        width: 100%;
        padding: 0.875rem 1.25rem;
        border: 2px solid #e2e8f0;
        border-radius: var(--radius-md);
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: white;
        cursor: pointer;
    }

    .modern-form-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .modern-form-textarea {
        min-height: 120px;
        resize: vertical;
    }

    .form-help-text {
        font-size: 0.85rem;
        color: #718096;
        margin-top: 0.5rem;
    }

    /* ============================================
       MODERN BUTTONS
       ============================================ */
    .btn-modern {
        padding: 0.875rem 1.75rem;
        font-weight: 600;
        border-radius: var(--radius-md);
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.95rem;
    }

    .btn-modern-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-modern-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .btn-modern-success {
        background: var(--gradient-success);
        color: white;
        box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
    }

    .btn-modern-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(17, 153, 142, 0.4);
        color: white;
    }

    .btn-modern-outline {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
    }

    .btn-modern-outline:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }

    .btn-modern-sm {
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
    }

    .btn-modern-lg {
        padding: 1.125rem 2.25rem;
        font-size: 1.1rem;
    }

    /* ============================================
       MODERN BADGES
       ============================================ */
    .badge-modern {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .badge-modern-primary {
        background: var(--gradient-primary);
        color: white;
    }

    .badge-modern-success {
        background: var(--gradient-success);
        color: white;
    }

    .badge-modern-warning {
        background: var(--gradient-warning);
        color: white;
    }

    .badge-modern-danger {
        background: var(--gradient-danger);
        color: white;
    }

    .badge-modern-info {
        background: var(--gradient-info);
        color: white;
    }

    /* ============================================
       MODERN INPUT GROUPS
       ============================================ */
    .modern-input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .modern-input-group-icon {
        position: absolute;
        left: 1rem;
        color: #a0aec0;
        z-index: 1;
    }

    .modern-input-group .modern-form-control {
        padding-left: 3rem;
    }

    .modern-input-group-append {
        margin-left: -1px;
    }

    /* ============================================
       MODERN ALERTS
       ============================================ */
    .alert-modern {
        padding: 1.25rem 1.5rem;
        border-radius: var(--radius-md);
        border: none;
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .alert-modern-success {
        background: rgba(17, 153, 142, 0.1);
        color: #11998e;
        border-left: 4px solid #11998e;
    }

    .alert-modern-danger {
        background: rgba(238, 9, 121, 0.1);
        color: #ee0979;
        border-left: 4px solid #ee0979;
    }

    .alert-modern-warning {
        background: rgba(240, 147, 251, 0.1);
        color: #f093fb;
        border-left: 4px solid #f093fb;
    }

    .alert-modern-info {
        background: rgba(52, 148, 230, 0.1);
        color: #3494E6;
        border-left: 4px solid #3494E6;
    }

    /* ============================================
       MODERN LIST ITEMS
       ============================================ */
    .modern-list-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        border-radius: var(--radius-md);
        background: white;
        margin-bottom: 0.75rem;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
        border: 1px solid #f1f5f9;
    }

    .modern-list-item:hover {
        transform: translateX(4px);
        box-shadow: var(--shadow-md);
        border-color: #667eea;
    }

    .modern-list-item-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        flex-shrink: 0;
    }

    .modern-list-item-content {
        flex: 1;
        min-width: 0;
    }

    .modern-list-item-title {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.25rem;
    }

    .modern-list-item-subtitle {
        font-size: 0.875rem;
        color: #718096;
    }

    /* ============================================
       MODERN EMPTY STATES
       ============================================ */
    .empty-state-modern {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2.5rem;
        color: #667eea;
    }

    .empty-state-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .empty-state-text {
        color: #718096;
        margin-bottom: 1.5rem;
    }

    /* ============================================
       MODERN STATS CARDS
       ============================================ */
    .stat-card-modern {
        background: white;
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        box-shadow: var(--shadow-sm);
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    .stat-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stat-card-modern:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .stat-card-modern:hover::before {
        transform: scaleX(1);
    }

    .stat-card-icon {
        width: 60px;
        height: 60px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: white;
        margin-bottom: 1rem;
    }

    .stat-card-number {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .stat-card-label {
        font-size: 0.95rem;
        color: #718096;
        font-weight: 500;
    }

    /* ============================================
       MODERN FILTERS
       ============================================ */
    .filter-card-modern {
        background: white;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
    }

    .filter-header-modern {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f7fafc;
    }

    .filter-header-modern i {
        color: #667eea;
        font-size: 1.25rem;
    }

    .filter-header-modern h5 {
        margin: 0;
        font-weight: 700;
        color: #2d3748;
    }

    /* ============================================
       MODERN PAGINATION
       ============================================ */
    .pagination-modern {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 2rem;
    }

    .pagination-modern .page-link {
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: var(--radius-md);
        color: #4a5568;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .pagination-modern .page-link:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
        transform: translateY(-2px);
    }

    .pagination-modern .page-item.active .page-link {
        background: var(--gradient-primary);
        border-color: transparent;
        color: white;
    }

    /* ============================================
       MODERN MODALS
       ============================================ */
    .modal-modern .modal-content {
        border-radius: var(--radius-xl);
        border: none;
        box-shadow: var(--shadow-lg);
    }

    .modal-modern .modal-header {
        background: var(--gradient-primary);
        color: white;
        border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        padding: 1.5rem 2rem;
        border-bottom: none;
    }

    .modal-modern .modal-title {
        font-weight: 700;
        font-size: 1.5rem;
    }

    .modal-modern .btn-close {
        filter: brightness(0) invert(1);
    }

    .modal-modern .modal-body {
        padding: 2rem;
    }

    .modal-modern .modal-footer {
        border-top: 2px solid #f7fafc;
        padding: 1.5rem 2rem;
    }

    /* ============================================
       MODERN TABS
       ============================================ */
    .nav-modern {
        border-bottom: 2px solid #f7fafc;
        margin-bottom: 1.5rem;
    }

    .nav-modern .nav-link {
        padding: 1rem 1.5rem;
        border: none;
        border-bottom: 3px solid transparent;
        color: #718096;
        font-weight: 600;
        transition: all 0.3s ease;
        border-radius: 0;
    }

    .nav-modern .nav-link:hover {
        color: #667eea;
        background: rgba(102, 126, 234, 0.05);
    }

    .nav-modern .nav-link.active {
        color: #667eea;
        border-bottom-color: #667eea;
        background: transparent;
    }

    /* ============================================
       ANIMATIONS
       ============================================ */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }

    .stagger-1 { animation-delay: 0.1s; }
    .stagger-2 { animation-delay: 0.2s; }
    .stagger-3 { animation-delay: 0.3s; }
    .stagger-4 { animation-delay: 0.4s; }

    /* ============================================
       UTILITY CLASSES
       ============================================ */
    .text-gradient-primary {
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .bg-gradient-primary {
        background: var(--gradient-primary);
    }

    .bg-gradient-success {
        background: var(--gradient-success);
    }

    .bg-gradient-info {
        background: var(--gradient-info);
    }

    .bg-gradient-warning {
        background: var(--gradient-warning);
    }

    .shadow-modern-sm {
        box-shadow: var(--shadow-sm);
    }

    .shadow-modern-md {
        box-shadow: var(--shadow-md);
    }

    .shadow-modern-lg {
        box-shadow: var(--shadow-lg);
    }

    /* ============================================
       RESPONSIVE
       ============================================ */
    @media (max-width: 768px) {
        .modern-page-header {
            padding: 1.5rem;
        }

        .modern-page-title {
            font-size: 1.5rem;
        }

        .modern-card {
            padding: 1.25rem;
        }

        .modern-table-wrapper {
            font-size: 0.875rem;
        }
    }
</style>

