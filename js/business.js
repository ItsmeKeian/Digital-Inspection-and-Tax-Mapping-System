const ROWS_PER_PAGE = 7;
let currentPage = 1;
let allBusinessRows = [];

$(document).ready(function(){
    loadBusinesses();

    $("#searchBusiness, #filterStatus, #filterBarangay, #filterDate").on("input change", function(){
        loadBusinesses(
            $("#searchBusiness").val(),
            $("#filterStatus").val(),
            $("#filterBarangay").val(),
            $("#filterDate").val()
        );
    });

    $("#clearFilters").on("click", function(){
        $("#searchBusiness").val("");
        $("#filterStatus").val("");
        $("#filterBarangay").val("");
        $("#filterDate").val("");
        loadBusinesses();
    });
});

function loadBusinesses(search="", status="", barangay="", date=""){
    $.get("php/get/get_businesses.php", { search, status, barangay, date }, function(data){
        let rows = JSON.parse(data);
        allBusinessRows = rows;
        currentPage = 1;
        renderBusinessPage();
    });
}

function renderBusinessPage(){
    let rows  = allBusinessRows;
    let total = rows.length;
    let totalPages = Math.ceil(total / ROWS_PER_PAGE);
    let start = (currentPage - 1) * ROWS_PER_PAGE;
    let end   = Math.min(start + ROWS_PER_PAGE, total);
    let html  = "";

    if(!total){
        html = `<tr><td colspan="6" class="table-empty">
            <i class="fas fa-store"></i>
            <span>No businesses found</span>
        </td></tr>`;
    } else {
        rows.slice(start, end).forEach(r => {
            let statusBadge = r.inspection_count > 0
                ? `<span class="badge-inspected"><i class="fas fa-check-circle"></i> Inspected</span>`
                : `<span class="badge-pending"><i class="fas fa-clock"></i> Pending</span>`;

            let date = r.created_at
                ? new Date(r.created_at).toLocaleDateString("en-US",{year:"numeric",month:"short",day:"2-digit"})
                : "—";

            html += `<tr>
                <td><strong>${r.business_name}</strong></td>
                <td>${r.owner_name || "—"}</td>
                <td>${statusBadge}</td>
                <td>${date}</td>
                <td><i class="fas fa-map-pin" style="color:#C8960C;margin-right:5px"></i>${r.barangay || "—"}</td>
                <td>
                    <button class="tbl-btn tbl-btn-view" title="View" onclick="viewBusiness(${r.id})"><i class="fas fa-eye"></i></button>
                    <button class="tbl-btn tbl-btn-edit" title="Edit" onclick="editBusiness(${r.id})"><i class="fas fa-edit"></i></button>
                    <button class="tbl-btn tbl-btn-delete" title="Delete" onclick="deleteBusiness(${r.id})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    }

    $("#businessTableBody").html(html);
    renderBusinessPagination(total, totalPages);
}

function renderBusinessPagination(total, totalPages){
    let wrap = document.getElementById("businessPaginationWrap");
    if(!wrap) return;

    if(totalPages <= 1){
        wrap.style.display = "none";
        return;
    }

    wrap.style.display = "flex";

    let start = (currentPage - 1) * ROWS_PER_PAGE + 1;
    let end   = Math.min(currentPage * ROWS_PER_PAGE, total);
    document.getElementById("businessPageInfo").textContent = `Showing ${start}–${end} of ${total}`;

    document.getElementById("businessPrevBtn").disabled = currentPage <= 1;
    document.getElementById("businessNextBtn").disabled = currentPage >= totalPages;

    let nums = "";
    for(let i = 1; i <= totalPages; i++){
        if(i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)){
            nums += `<button class="page-num ${i === currentPage ? "active" : ""}" onclick="goBusinessPage(${i})">${i}</button>`;
        } else if(i === currentPage - 3 || i === currentPage + 3){
            nums += `<span class="page-ellipsis">…</span>`;
        }
    }
    document.getElementById("businessPageNumbers").innerHTML = nums;
}

function goBusinessPage(page){
    currentPage = page;
    renderBusinessPage();
    window.scrollTo({top:0, behavior:"smooth"});
}

function changeBusinessPage(dir){
    let totalPages = Math.ceil(allBusinessRows.length / ROWS_PER_PAGE);
    currentPage = Math.max(1, Math.min(currentPage + dir, totalPages));
    renderBusinessPage();
    window.scrollTo({top:0, behavior:"smooth"});
}

function viewBusiness(id){
    window.open("php/view/view_inspection.php?business_id=" + id, "_blank");
}

function deleteBusiness(id){
    if(confirm("Are you sure you want to delete this business? This cannot be undone.")){
        $.post("php/delete/delete_business.php", { id }, function(){
            loadBusinesses(
                $("#searchBusiness").val(),
                $("#filterStatus").val(),
                $("#filterBarangay").val(),
                $("#filterDate").val()
            );
        });
    }
}

function editBusiness(id){
    $.get("php/get/get_single_business.php", { id }, function(data){
        let r = JSON.parse(data);
        $("#edit_id").val(r.id);
        $("#edit_business_name").val(r.business_name);
        $("#edit_owner_name").val(r.owner_name);
        $("#edit_barangay").val(r.barangay);
        $("#edit_contact").val(r.contact_number);
        new bootstrap.Modal(document.getElementById("editModal")).show();
    });
}

function updateBusiness(){
    $.post("php/update/update_business.php", {
        id:             $("#edit_id").val(),
        business_name:  $("#edit_business_name").val(),
        owner_name:     $("#edit_owner_name").val(),
        barangay:       $("#edit_barangay").val(),
        contact_number: $("#edit_contact").val()
    }, function(){
        bootstrap.Modal.getInstance(document.getElementById("editModal")).hide();
        loadBusinesses(
            $("#searchBusiness").val(),
            $("#filterStatus").val(),
            $("#filterBarangay").val(),
            $("#filterDate").val()
        );
    });
}