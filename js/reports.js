const ROWS_PER_PAGE = 7;
let currentPage = 1;
let allReportRows = [];
let currentChartData = null;

$(document).ready(function(){
    loadReports();

    $("#searchReports").on("keyup", function(){ loadReports(); });
    $("#filterBarangay, #filterStatus, #fromDate, #toDate").on("change", function(){ loadReports(); });
});

let lineChart, pieChart;

function loadReports(){
    $.get("php/get/get_reports.php", {
        barangay: $("#filterBarangay").val(),
        status:   $("#filterStatus").val(),
        from:     $("#fromDate").val(),
        to:       $("#toDate").val(),
        search:   $("#searchReports").val()
    }, function(data){
        let d = JSON.parse(data);
        allReportRows      = d.rows;
        currentChartData   = d;
        currentPage        = 1;
        renderReportPage();
        renderCharts(d);
    });
}

function renderReportPage(){
    let rows       = allReportRows;
    let total      = rows.length;
    let totalPages = Math.ceil(total / ROWS_PER_PAGE);
    let start      = (currentPage - 1) * ROWS_PER_PAGE;
    let end        = Math.min(start + ROWS_PER_PAGE, total);
    let html       = "";

    if(!total){
        html = `<tr><td colspan="6" class="table-empty">
            <i class="fas fa-chart-bar"></i>
            <span>No records found for the selected filters</span>
        </td></tr>`;
    } else {
        rows.slice(start, end).forEach(r => {
            let statusBadge = getStatusBadge(r.status);
            let noticeBtn   = "";
            if(r.status === "Violation"){
                noticeBtn = `<a class="btn-notice-violation"
                    href="php/view/notice_of_violation.php?id=${r.id}"
                    target="_blank" title="Generate Notice of Violation">
                    <i class="fas fa-file-alt"></i> Notice
                </a>`;
            }

            html += `<tr>
                <td><strong>${r.business_name}</strong></td>
                <td>${r.owner_name || "—"}</td>
                <td><i class="fas fa-map-pin" style="color:#C8960C;margin-right:4px"></i>${r.barangay || "—"}</td>
                <td>${r.date_of_inspection || "—"}</td>
                <td>${statusBadge}</td>
                <td style="white-space:nowrap">
                    <button class="tbl-btn tbl-btn-view" title="View" onclick="viewReport(${r.id})"><i class="fas fa-eye"></i></button>
                    <button class="tbl-btn tbl-btn-edit" title="Edit" onclick="editReport(${r.id})"><i class="fas fa-edit"></i></button>
                    <button class="tbl-btn tbl-btn-delete" title="Delete" onclick="deleteReport(${r.id})"><i class="fas fa-trash"></i></button>
                    ${noticeBtn}
                </td>
            </tr>`;
        });
    }

    $("#reportTableBody").html(html);
    $("#resultCount").text(total + " record(s) found");
    renderReportPagination(total, totalPages);
}

function renderReportPagination(total, totalPages){
    let wrap = document.getElementById("reportPaginationWrap");
    if(!wrap) return;

    if(totalPages <= 1){
        wrap.style.display = "none";
        return;
    }

    wrap.style.display = "flex";

    let start = (currentPage - 1) * ROWS_PER_PAGE + 1;
    let end   = Math.min(currentPage * ROWS_PER_PAGE, total);
    document.getElementById("reportPageInfo").textContent = `Showing ${start}–${end} of ${total}`;

    document.getElementById("reportPrevBtn").disabled = currentPage <= 1;
    document.getElementById("reportNextBtn").disabled = currentPage >= totalPages;

    let nums = "";
    for(let i = 1; i <= totalPages; i++){
        if(i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)){
            nums += `<button class="page-num ${i === currentPage ? "active" : ""}" onclick="goReportPage(${i})">${i}</button>`;
        } else if(i === currentPage - 3 || i === currentPage + 3){
            nums += `<span class="page-ellipsis">…</span>`;
        }
    }
    document.getElementById("reportPageNumbers").innerHTML = nums;
}

function goReportPage(page){
    currentPage = page;
    renderReportPage();
    window.scrollTo({top:0, behavior:"smooth"});
}

function changeReportPage(dir){
    let totalPages = Math.ceil(allReportRows.length / ROWS_PER_PAGE);
    currentPage = Math.max(1, Math.min(currentPage + dir, totalPages));
    renderReportPage();
    window.scrollTo({top:0, behavior:"smooth"});
}

function getStatusBadge(status){
    if(status === "Inspected")
        return '<span class="badge-report-inspected"><i class="fas fa-check-circle"></i> Inspected</span>';
    if(status === "Pending")
        return '<span class="badge-report-pending"><i class="fas fa-clock"></i> Pending</span>';
    if(status === "Violation")
        return '<span class="badge-report-violation"><i class="fas fa-exclamation-circle"></i> Violation</span>';
    return '<span class="badge-report-pending">—</span>';
}

function renderCharts(d){
    if(lineChart) lineChart.destroy();
    if(pieChart)  pieChart.destroy();

    lineChart = new Chart(document.getElementById("reportLineChart"), {
        type: "line",
        data: {
            labels: d.months,
            datasets: [{
                label: "Inspections",
                data: d.monthly,
                borderColor: "#C8960C",
                backgroundColor: function(context){
                    let chart = context.chart;
                    let {ctx, chartArea} = chart;
                    if(!chartArea) return "transparent";
                    let gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, "rgba(200,150,12,0.18)");
                    gradient.addColorStop(1, "rgba(200,150,12,0)");
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                borderWidth: 2.5,
                pointBackgroundColor: "#C8960C",
                pointBorderColor: "#fff",
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "#1C1400",
                    titleColor: "#C8960C",
                    bodyColor: "#fff",
                    padding: 10,
                    cornerRadius: 8
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: "#F3F4F6" }, ticks: { color: "#9CA3AF", font: { size: 11 } } },
                x: { grid: { display: false }, ticks: { color: "#9CA3AF", font: { size: 11 } } }
            }
        }
    });

    pieChart = new Chart(document.getElementById("reportPieChart"), {
        type: "doughnut",
        data: {
            labels: ["Inspected", "Pending", "Violation"],
            datasets: [{
                data: [d.inspected, d.pending, d.violations],
                backgroundColor: ["#C8960C", "#F5C518", "#EF4444"],
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: { font: { size: 12 }, padding: 14, usePointStyle: true, color: "#6B7280" }
                },
                tooltip: {
                    backgroundColor: "#1C1400",
                    titleColor: "#C8960C",
                    bodyColor: "#fff",
                    padding: 10,
                    cornerRadius: 8
                }
            },
            cutout: "65%"
        }
    });
}

function viewReport(id){
    window.open("php/view/view_inspection.php?id=" + id, "_blank");
}

function editReport(id){
    window.location = "inspections.php?edit=" + id;
}

function deleteReport(id){
    if(!confirm("Delete this inspection record? This cannot be undone.")) return;
    $.post("php/delete/delete_inspection.php", { id }, function(){
        loadReports();
    });
}

function exportReports(){
    let params = new URLSearchParams({
        barangay: $("#filterBarangay").val(),
        status:   $("#filterStatus").val(),
        from:     $("#fromDate").val(),
        to:       $("#toDate").val(),
        search:   $("#searchReports").val()
    });
    window.open("php/export/export_reports.php?" + params.toString(), "_blank");
}