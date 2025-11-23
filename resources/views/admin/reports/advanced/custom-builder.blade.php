@extends('admin.layouts.app')

@section('title', 'Custom Report Builder')

@section('styles')
<style>
.report-builder {
    background: #f8f9fc;
    border-radius: 10px;
    padding: 20px;
}

.builder-step {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    border-left: 4px solid #4e73df;
}

.step-number {
    background: #4e73df;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 10px;
}

.table-selector {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.table-selector:hover {
    border-color: #4e73df;
    background: #f8f9fc;
}

.table-selector.selected {
    border-color: #4e73df;
    background: #e3f2fd;
}

.column-checkbox {
    margin: 5px 0;
}

.filter-row {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
}

.chart-preview {
    height: 300px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    background: white;
}

.saved-reports {
    max-height: 400px;
    overflow-y: auto;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cogs mr-2"></i>Custom Report Builder
        </h1>
        <div class="btn-group">
            <a href="{{ route('admin.advanced-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Back to Reports
            </a>
            <button type="button" class="btn btn-success" onclick="generateReport()">
                <i class="fas fa-play mr-1"></i>Generate Report
            </button>
            <button type="button" class="btn btn-primary" onclick="saveReport()">
                <i class="fas fa-save mr-1"></i>Save Report
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Report Builder Form -->
        <div class="col-lg-8">
            <div class="report-builder">
                <form id="reportBuilderForm">
                    <!-- Step 1: Report Basic Info -->
                    <div class="builder-step">
                        <h5>
                            <span class="step-number">1</span>
                            Report Information
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_name">Report Name</label>
                                    <input type="text" class="form-control" id="report_name" name="report_name" placeholder="Enter report name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_description">Description</label>
                                    <input type="text" class="form-control" id="report_description" name="report_description" placeholder="Brief description">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Table Selection -->
                    <div class="builder-step">
                        <h5>
                            <span class="step-number">2</span>
                            Select Data Sources
                        </h5>
                        <div class="row">
                            @foreach($availableTables as $tableKey => $table)
                            <div class="col-md-4 mb-3">
                                <div class="table-selector" onclick="selectTable('{{ $tableKey }}')" id="table_{{ $tableKey }}">
                                    <i class="fas fa-table fa-2x mb-2 text-primary"></i>
                                    <h6>{{ $table['name'] }}</h6>
                                    <small class="text-muted">{{ count($table['columns']) }} columns</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <input type="hidden" id="selected_tables" name="tables">
                    </div>

                    <!-- Step 3: Column Selection -->
                    <div class="builder-step">
                        <h5>
                            <span class="step-number">3</span>
                            Select Columns
                        </h5>
                        <div id="column_selection">
                            <p class="text-muted">Please select data sources first</p>
                        </div>
                    </div>

                    <!-- Step 4: Date Range -->
                    <div class="builder-step">
                        <h5>
                            <span class="step-number">4</span>
                            Date Range (Optional)
                        </h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_range">Date Range</label>
                                    <select class="form-control" id="date_range" name="date_range" onchange="toggleCustomDateRange()">
                                        <option value="">No date filter</option>
                                        <option value="today">Today</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                        <option value="year">This Year</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4" id="custom_date_from_group" style="display:none;">
                                <div class="form-group">
                                    <label for="custom_date_from">From Date</label>
                                    <input type="text" class="form-control" id="custom_date_from" name="custom_date_from"
                                           placeholder="dd-mm-yyyy" 
                                           pattern="\d{2}-\d{2}-\d{4}" 
                                           maxlength="10">
                                    <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
                                </div>
                            </div>
                            <div class="col-md-4" id="custom_date_to_group" style="display:none;">
                                <div class="form-group">
                                    <label for="custom_date_to">To Date</label>
                                    <input type="text" class="form-control" id="custom_date_to" name="custom_date_to"
                                           placeholder="dd-mm-yyyy" 
                                           pattern="\d{2}-\d{2}-\d{4}" 
                                           maxlength="10">
                                    <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Filters -->
                    <div class="builder-step">
                        <h5>
                            <span class="step-number">5</span>
                            Filters (Optional)
                        </h5>
                        <div id="filters_container">
                            <!-- Filters will be dynamically added here -->
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addFilter()">
                            <i class="fas fa-plus mr-1"></i>Add Filter
                        </button>
                    </div>

                    <!-- Step 6: Grouping & Sorting -->
                    <div class="builder-step">
                        <h5>
                            <span class="step-number">6</span>
                            Grouping & Sorting (Optional)
                        </h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="group_by">Group By</label>
                                    <select class="form-control" id="group_by" name="group_by[]" multiple>
                                        <!-- Options populated dynamically -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="order_by">Order By</label>
                                    <select class="form-control" id="order_by" name="order_by">
                                        <option value="">No ordering</option>
                                        <!-- Options populated dynamically -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="order_direction">Order Direction</label>
                                    <select class="form-control" id="order_direction" name="order_direction">
                                        <option value="asc">Ascending</option>
                                        <option value="desc">Descending</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 7: Visualization -->
                    <div class="builder-step">
                        <h5>
                            <span class="step-number">7</span>
                            Visualization
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="chart_type">Display Type</label>
                                    <select class="form-control" id="chart_type" name="chart_type" onchange="updateChartPreview()">
                                        <option value="table">Table</option>
                                        <option value="bar">Bar Chart</option>
                                        <option value="line">Line Chart</option>
                                        <option value="pie">Pie Chart</option>
                                        <option value="column">Column Chart</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="save_report" name="save_report">
                                        <label class="custom-control-label" for="save_report">Save this report for future use</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Saved Reports -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Saved Reports</h6>
                </div>
                <div class="card-body saved-reports">
                    @if($savedReports->count() > 0)
                        @foreach($savedReports as $report)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                            <div>
                                <h6 class="mb-0">{{ $report->name }}</h6>
                                <small class="text-muted">{{ formatDate($report->created_at) }}</small>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="loadSavedReport({{ $report->id }})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-success" onclick="runSavedReport({{ $report->id }})">
                                    <i class="fas fa-play"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No saved reports yet</p>
                    @endif
                </div>
            </div>

            <!-- Help & Tips -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tips & Help</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><i class="fas fa-lightbulb text-warning mr-2"></i>Pro Tips</h6>
                        <ul class="small">
                            <li>Start with a single table for simple reports</li>
                            <li>Use filters to narrow down your data</li>
                            <li>Group by categorical columns for summaries</li>
                            <li>Save frequently used reports</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <h6><i class="fas fa-info-circle text-info mr-2"></i>Column Types</h6>
                        <ul class="small">
                            <li><strong>Text:</strong> Names, descriptions</li>
                            <li><strong>Number:</strong> Counts, amounts</li>
                            <li><strong>Date:</strong> Creation dates, appointments</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Results Modal -->
<div class="modal fade" id="reportResultsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Results</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="reportResults">
                    <!-- Results will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="exportReport('excel')">
                    <i class="fas fa-file-excel mr-1"></i>Export Excel
                </button>
                <button type="button" class="btn btn-danger" onclick="exportReport('pdf')">
                    <i class="fas fa-file-pdf mr-1"></i>Export PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display:none;">
    <div class="text-center text-white">
        <div class="spinner-border mb-3" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <h5>Generating Report...</h5>
        <p>Please wait while we process your request</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
let selectedTables = [];
let availableTables = @json($availableTables);
let currentReportData = null;

function selectTable(tableKey) {
    const tableElement = document.getElementById(`table_${tableKey}`);
    
    if (selectedTables.includes(tableKey)) {
        // Deselect table
        selectedTables = selectedTables.filter(t => t !== tableKey);
        tableElement.classList.remove('selected');
    } else {
        // Select table (limit to 3 tables for performance)
        if (selectedTables.length >= 3) {
            alert('Maximum 3 tables can be selected');
            return;
        }
        selectedTables.push(tableKey);
        tableElement.classList.add('selected');
    }
    
    document.getElementById('selected_tables').value = JSON.stringify(selectedTables);
    updateColumnSelection();
    updateGroupByOptions();
}

function updateColumnSelection() {
    const container = document.getElementById('column_selection');
    
    if (selectedTables.length === 0) {
        container.innerHTML = '<p class="text-muted">Please select data sources first</p>';
        return;
    }
    
    let html = '';
    selectedTables.forEach(tableKey => {
        const table = availableTables[tableKey];
        html += `<div class="mb-3">
            <h6>${table.name} Columns:</h6>
            <div class="row">`;
        
        table.columns.forEach(column => {
            html += `<div class="col-md-6">
                <div class="custom-control custom-checkbox column-checkbox">
                    <input type="checkbox" class="custom-control-input" id="col_${tableKey}_${column}" name="columns[]" value="${tableKey}.${column}">
                    <label class="custom-control-label" for="col_${tableKey}_${column}">${column}</label>
                </div>
            </div>`;
        });
        
        html += '</div></div>';
    });
    
    container.innerHTML = html;
}

function updateGroupByOptions() {
    const groupBySelect = document.getElementById('group_by');
    const orderBySelect = document.getElementById('order_by');
    
    // Clear existing options
    groupBySelect.innerHTML = '';
    orderBySelect.innerHTML = '<option value="">No ordering</option>';
    
    selectedTables.forEach(tableKey => {
        const table = availableTables[tableKey];
        table.columns.forEach(column => {
            const option = new Option(`${table.name}: ${column}`, `${tableKey}.${column}`);
            const option2 = new Option(`${table.name}: ${column}`, `${tableKey}.${column}`);
            groupBySelect.add(option);
            orderBySelect.add(option2);
        });
    });
}

function toggleCustomDateRange() {
    const dateRange = document.getElementById('date_range').value;
    const fromGroup = document.getElementById('custom_date_from_group');
    const toGroup = document.getElementById('custom_date_to_group');
    
    if (dateRange === 'custom') {
        fromGroup.style.display = 'block';
        toGroup.style.display = 'block';
    } else {
        fromGroup.style.display = 'none';
        toGroup.style.display = 'none';
    }
}

function addFilter() {
    const container = document.getElementById('filters_container');
    const filterCount = container.children.length;
    
    let columnOptions = '';
    selectedTables.forEach(tableKey => {
        const table = availableTables[tableKey];
        table.columns.forEach(column => {
            columnOptions += `<option value="${tableKey}.${column}">${table.name}: ${column}</option>`;
        });
    });
    
    const filterHtml = `
        <div class="filter-row" id="filter_${filterCount}">
            <div class="row">
                <div class="col-md-4">
                    <select class="form-control" name="filters[${filterCount}][column]">
                        <option value="">Select Column</option>
                        ${columnOptions}
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="filters[${filterCount}][operator]">
                        <option value="=">=</option>
                        <option value="!=">!=</option>
                        <option value=">">></option>
                        <option value="<"><</option>
                        <option value=">=">>=</option>
                        <option value="<="><=</option>
                        <option value="LIKE">Contains</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="filters[${filterCount}][value]" placeholder="Value">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeFilter(${filterCount})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', filterHtml);
}

function removeFilter(filterId) {
    const filterElement = document.getElementById(`filter_${filterId}`);
    if (filterElement) {
        filterElement.remove();
    }
}

function generateReport() {
    const formData = new FormData(document.getElementById('reportBuilderForm'));
    
    // Validate required fields
    if (!formData.get('report_name')) {
        alert('Please enter a report name');
        return;
    }
    
    if (selectedTables.length === 0) {
        alert('Please select at least one data source');
        return;
    }
    
    const selectedColumns = Array.from(document.querySelectorAll('input[name="columns[]"]:checked')).map(cb => cb.value);
    if (selectedColumns.length === 0) {
        alert('Please select at least one column');
        return;
    }
    
    // Show loading overlay
    document.getElementById('loadingOverlay').style.display = 'flex';
    
    // Prepare request data
    const requestData = {
        report_name: formData.get('report_name'),
        tables: selectedTables,
        columns: selectedColumns,
        date_range: formData.get('date_range'),
        custom_date_from: formData.get('custom_date_from'),
        custom_date_to: formData.get('custom_date_to'),
        filters: [],
        group_by: Array.from(document.getElementById('group_by').selectedOptions).map(o => o.value),
        order_by: formData.get('order_by'),
        order_direction: formData.get('order_direction'),
        chart_type: formData.get('chart_type'),
        save_report: document.getElementById('save_report').checked
    };
    
    // Collect filters
    const filterRows = document.querySelectorAll('.filter-row');
    filterRows.forEach((row, index) => {
        const column = row.querySelector('select[name*="[column]"]').value;
        const operator = row.querySelector('select[name*="[operator]"]').value;
        const value = row.querySelector('input[name*="[value]"]').value;
        
        if (column && operator && value) {
            requestData.filters.push({ column, operator, value });
        }
    });
    
    // Make AJAX request
    fetch('{{ route("admin.advanced-reports.custom-reports.generate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loadingOverlay').style.display = 'none';
        
        if (data.success) {
            currentReportData = data;
            displayReportResults(data);
            $('#reportResultsModal').modal('show');
        } else {
            alert('Error generating report: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        document.getElementById('loadingOverlay').style.display = 'none';
        alert('Error generating report: ' + error.message);
    });
}

function displayReportResults(data) {
    const container = document.getElementById('reportResults');
    
    let html = `
        <div class="mb-3">
            <h6>Report Summary</h6>
            <p class="text-muted">
                Total Records: ${data.total_records} | 
                Tables: ${data.query_info.tables.join(', ')} | 
                Columns: ${data.query_info.columns.length}
            </p>
        </div>
    `;
    
    if (data.chart_data && data.chart_data.type !== 'table') {
        html += `
            <div class="mb-4">
                <canvas id="reportChart" width="400" height="200"></canvas>
            </div>
        `;
    }
    
    // Table view
    if (data.data.length > 0) {
        html += '<div class="table-responsive">';
        html += '<table class="table table-striped table-sm">';
        
        // Header
        html += '<thead><tr>';
        Object.keys(data.data[0]).forEach(key => {
            html += `<th>${key}</th>`;
        });
        html += '</tr></thead>';
        
        // Body (limit to first 100 rows for display)
        html += '<tbody>';
        data.data.slice(0, 100).forEach(row => {
            html += '<tr>';
            Object.values(row).forEach(value => {
                html += `<td>${value || '-'}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody>';
        
        html += '</table>';
        html += '</div>';
        
        if (data.data.length > 100) {
            html += `<p class="text-muted">Showing first 100 of ${data.total_records} records</p>`;
        }
    } else {
        html += '<p class="text-center text-muted">No data found for the specified criteria</p>';
    }
    
    container.innerHTML = html;
    
    // Render chart if applicable
    if (data.chart_data && data.chart_data.type !== 'table') {
        setTimeout(() => renderChart(data.chart_data), 100);
    }
}

function renderChart(chartData) {
    const ctx = document.getElementById('reportChart').getContext('2d');
    
    new Chart(ctx, {
        type: chartData.type === 'column' ? 'bar' : chartData.type,
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Data',
                data: chartData.data,
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                    '#858796', '#5a5c69', '#1f2937', '#047857', '#7c3aed'
                ],
                borderColor: '#4e73df',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function exportReport(format) {
    if (!currentReportData) {
        alert('No report data to export');
        return;
    }
    
    // Create export URL with report ID
    const reportId = currentReportData.report_id || 'temp';
    const exportUrl = `{{ route('admin.advanced-reports.export', '') }}/${reportId}?format=${format}`;
    
    // Create a temporary link to download the report
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = `${document.getElementById('report_name').value || 'report'}.${format}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function saveReport() {
    if (!currentReportData) {
        alert('Please generate a report first');
        return;
    }
    
    document.getElementById('save_report').checked = true;
    generateReport();
}
</script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection
