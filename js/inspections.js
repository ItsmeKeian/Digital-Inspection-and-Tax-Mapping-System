const ROWS_PER_PAGE = 7;
let currentPage = 1;
let allInspectionRows = [];

$(document).ready(function(){
    loadInspections();
    loadBusinessOptions();
});

function loadInspections(search="", barangay="", type="", date=""){
    $.get("php/get/get_inspections.php", { search, barangay, type, date }, function(data){
        allInspectionRows = JSON.parse(data);
        currentPage = 1;
        renderInspectionPage();
    });
}

function renderInspectionPage(){
    let rows       = allInspectionRows;
    let total      = rows.length;
    let totalPages = Math.ceil(total / ROWS_PER_PAGE);
    let start      = (currentPage - 1) * ROWS_PER_PAGE;
    let end        = Math.min(start + ROWS_PER_PAGE, total);
    let html       = "";

    if(!total){
        html = `<tr><td colspan="8" class="table-empty">
            <i class="fas fa-search"></i>
            <span>No inspection records found</span>
        </td></tr>`;
    } else {
        rows.slice(start, end).forEach(r => {
            let statusMap = {
                "Existing":     '<span class="badge-active"><i class="fas fa-circle"></i> Active</span>',
                "New":          '<span class="badge-new-status"><i class="fas fa-circle"></i> New</span>',
                "Unregistered": '<span class="badge-unregistered"><i class="fas fa-circle"></i> Unregistered</span>',
                "Closed":       '<span class="badge-closed"><i class="fas fa-circle"></i> Closed</span>',
                "Transferred":  '<span class="badge-transferred"><i class="fas fa-circle"></i> Transferred</span>',
            };
            let statusBadge = statusMap[r.operation_status] || '<span class="badge-active"><i class="fas fa-circle"></i> Active</span>';

            let findingsBadge = '<span class="badge-ok"><i class="fas fa-check"></i> OK</span>';
            if(r.no_mayor_permit == 1)
                findingsBadge = '<span class="badge-no-permit"><i class="fas fa-times"></i> No Permit</span>';
            else if(r.expired_permit == 1)
                findingsBadge = '<span class="badge-expired"><i class="fas fa-clock"></i> Expired</span>';

            html += `<tr>
                <td><strong>${r.business_name}</strong></td>
                <td>${r.owner_name || "—"}</td>
                <td>${r.barangay || "—"}</td>
                <td>${r.date_of_inspection || "—"}</td>
                <td>${r.type_of_business || "—"}</td>
                <td>${statusBadge}</td>
                <td>${findingsBadge}</td>
                <td style="white-space:nowrap">
                    <button class="tbl-btn tbl-btn-view" title="View & Print" onclick="viewInspection(${r.id})"><i class="fas fa-eye"></i></button>
                    <button class="tbl-btn tbl-btn-edit" title="Edit" onclick="editInspection(${r.id})"><i class="fas fa-edit"></i></button>
                    <button class="tbl-btn tbl-btn-delete" title="Delete" onclick="deleteInspection(${r.id})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    }

    $("#inspectionTable").html(html);
    renderInspectionPagination(total, totalPages);
}

function renderInspectionPagination(total, totalPages){
    let wrap = document.getElementById("inspectionPaginationWrap");
    if(!wrap) return;

    if(totalPages <= 1){
        wrap.style.display = "none";
        return;
    }

    wrap.style.display = "flex";

    let start = (currentPage - 1) * ROWS_PER_PAGE + 1;
    let end   = Math.min(currentPage * ROWS_PER_PAGE, total);
    document.getElementById("inspectionPageInfo").textContent = `Showing ${start}–${end} of ${total}`;

    document.getElementById("inspectionPrevBtn").disabled = currentPage <= 1;
    document.getElementById("inspectionNextBtn").disabled = currentPage >= totalPages;

    let nums = "";
    for(let i = 1; i <= totalPages; i++){
        if(i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)){
            nums += `<button class="page-num ${i === currentPage ? "active" : ""}" onclick="goInspectionPage(${i})">${i}</button>`;
        } else if(i === currentPage - 3 || i === currentPage + 3){
            nums += `<span class="page-ellipsis">…</span>`;
        }
    }
    document.getElementById("inspectionPageNumbers").innerHTML = nums;
}

function goInspectionPage(page){
    currentPage = page;
    renderInspectionPage();
    window.scrollTo({top:0, behavior:"smooth"});
}

function changeInspectionPage(dir){
    let totalPages = Math.ceil(allInspectionRows.length / ROWS_PER_PAGE);
    currentPage = Math.max(1, Math.min(currentPage + dir, totalPages));
    renderInspectionPage();
    window.scrollTo({top:0, behavior:"smooth"});
}

function loadBusinessOptions(){
    $.get("php/get/get_businesses.php", {}, function(data){
        let rows = JSON.parse(data);
        let html = '<option value="">— Type to search business —</option>';
        rows.forEach(r => {
            html += `<option value="${r.id}"
                data-name="${r.business_name}"
                data-owner="${r.owner_name || ''}"
                data-barangay="${r.barangay || ''}">
                ${r.business_name}
            </option>`;
        });
        $("#selectBusiness").html(html);

        if($("#selectBusiness").length){
            $("#selectBusiness").select2({
                placeholder: "Type to search business...",
                allowClear: true,
                width: "100%",
                dropdownParent: $("#addInspectionModal"),
                language: {
                    noResults: function(){ return "No business found"; },
                    searching: function(){ return "Searching..."; }
                }
            });
        }
    });
}

$(document).on("select2:select", "#selectBusiness", function(){
    let selected = $(this).find(":selected");
    $("#inspection_business_id").val($(this).val());
    $("#business_name").val(selected.data("name"));
    $("#owner_name").val(selected.data("owner"));
    $("#barangay").val(selected.data("barangay"));
});

$(document).on("select2:clear", "#selectBusiness", function(){
    $("#inspection_business_id, #business_name, #owner_name, #barangay").val("");
});

function openAddModal(){
    $("#inspectionForm")[0].reset();
    $("#inspection_id").val("");
    if($("#selectBusiness").hasClass("select2-hidden-accessible")){
        $("#selectBusiness").val("").trigger("change");
    }
    $("#inspectionForm").attr("action", "php/create/create_inspection.php");
    new bootstrap.Modal(document.getElementById("addInspectionModal")).show();
}

function viewInspection(id){
    window.open("php/view/view_inspection.php?id=" + id, "_blank");
}

function editInspection(id){
    $.get("php/get/get_single_inspection.php?id=" + id, function(data){
        let r = JSON.parse(data);
        $("#inspection_id").val(r.id);
        $("input[name=date_of_inspection]").val(r.date_of_inspection);
        $("input[name=time_of_inspection]").val(r.time_of_inspection);
        $("#barangay").val(r.barangay);
        $("#inspection_business_id").val(r.business_id);
        $("#business_name").val(r.business_name);
        $("#selectBusiness").val(r.business_id);
        $("input[name=trade_name]").val(r.trade_name);
        $("#owner_name").val(r.owner_name);
        $("input[name=contact_number]").val(r.contact_number);
        $("select[name=mayor_permit]").val(r.mayor_permit);
        $("select[name=barangay_clearance]").val(r.barangay_clearance);
        $("select[name=dti_sec_cda]").val(r.dti_sec_cda);
        $("select[name=bir_registration]").val(r.bir_registration);
        $("input[name=permit_number]").val(r.permit_number);
        $("input[name=year_last_registered]").val(r.year_last_registered);
        $("textarea[name=declared_nature]").val(r.declared_nature);
        $("textarea[name=actual_nature]").val(r.actual_nature);
        $("input[name=activity_matches]").prop("checked", r.activity_matches == 1);
        $("input[name=activity_not_match]").prop("checked", r.activity_not_match == 1);
        $("input[name=psic_code]").val(r.psic_code);
        $("select[name=type_of_business]").val(r.type_of_business);
        $("select[name=operation_status]").val(r.operation_status);
        $("input[name=floor_area]").val(r.floor_area);
        $("input[name=male_employees]").val(r.male_employees);
        $("input[name=female_employees]").val(r.female_employees);
        $("select[name=sanitary_permit]").val(r.sanitary_permit);
        $("select[name=fire_cert]").val(r.fire_cert);
        $("select[name=permit_displayed]").val(r.permit_displayed);
        $("select[name=additional_support]").val(r.additional_support);
        $("textarea[name=remarks]").val(r.remarks);
        $("input[name=no_mayor_permit]").prop("checked", r.no_mayor_permit == 1);
        $("input[name=expired_permit]").prop("checked", r.expired_permit == 1);
        $("input[name=change_nature]").prop("checked", r.change_nature == 1);
        $("input[name=change_address]").prop("checked", r.change_address == 1);
        $("input[name=additional_line]").prop("checked", r.additional_line == 1);
        $("input[name=others]").val(r.others);
        $("input[name=notice_register]").prop("checked", r.notice_register == 1);
        $("input[name=notice_violation]").prop("checked", r.notice_violation == 1);
        $("input[name=reassessment]").prop("checked", r.reassessment == 1);
        $("input[name=compliance_days]").val(r.compliance_days);
        $("input[name=referred_to]").val(r.referred_to);
        $("textarea[name=action_remarks]").val(r.action_remarks);
        $("input[name=inspector_name]").val(r.inspector_name);
        $("input[name=date_signed]").val(r.date_signed);
        $("#inspectionForm").attr("action", "php/update/update_inspection.php");
        new bootstrap.Modal(document.getElementById("addInspectionModal")).show();
    });
}

function deleteInspection(id){
    if(!confirm("Delete this inspection record? This cannot be undone.")) return;
    $.post("php/delete/delete_inspection.php", { id }, function(){
        loadInspections(
            $("#searchInspection").val(),
            $("#filterBarangay").val(),
            $("#filterType").val(),
            $("#filterDate").val()
        );
    });
}

function exportExcel(){
    window.location = "php/export/export_excel.php";
}