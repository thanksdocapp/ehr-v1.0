<style>

.form-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
}

.form-section-header {
    background: #f8f9fc;
    color: #2d3748;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
    border-bottom: 2px solid #e2e8f0;
}

.form-section-header h1,
.form-section-header h2,
.form-section-header h3,
.form-section-header h4,
.form-section-header h5,
.form-section-header h6 {
    color: #1a202c;
    font-weight: 700;
}

.form-section-header i {
    color: #1a202c;
}

.form-section-header small {
    color: #4a5568;
}

.form-section-body {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control, .form-select {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #1cc88a;
    box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
}

.btn {
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
}

.variable-tag {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    margin: 0.2rem;
    display: inline-block;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.variable-tag:hover {
    transform: scale(1.05);
}

.category-card {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.category-card:hover {
    border-color: #1cc88a;
    background: #f8f9fc;
}

.category-card.active {
    border-color: #1cc88a;
    background: linear-gradient(135deg, rgba(28, 200, 138, 0.1) 0%, rgba(54, 185, 204, 0.1) 100%);
}

.editor-container {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    overflow: hidden;
}

.form-help {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.5rem;
    font-style: italic;
}

.admin-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #e3e6f0;
}

.page-title {
    margin-bottom: 2rem;
}

.page-title h1 {
    color: #5a5c69;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.page-subtitle {
    color: #858796;
    font-size: 1.1rem;
    margin: 0;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
