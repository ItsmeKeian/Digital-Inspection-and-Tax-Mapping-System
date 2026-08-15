$(document).ready(function(){
    loadInspections();
    loadBusinessOptions();
});

function loadInspections(search="", barangay="", type="", date=""){
    $.get("php/get/get_inspections.php", { search, barangay, type, date }, function(data){
        let rows = JSON.parse(data);
        let html = "";

        if(!rows.length){
            html = `<tr><td colspan="8" class="table-empty">
                <i class="fas fa-search"></i>
                <span>No inspection records found</span>
            </td></tr>`;
        } else {
            rows.forEach(r => {

                // Status badge
                let statusMap = {
                    "Existing":     '<span class="badge-active"><i class="fas fa-circle"></i> Active</span>',
                    "New":          '<span class="badge-new-status"><i class="fas fa-circle"></i> New</span>',
                    "Unregistered": '<span class="badge-unregistered"><i class="fas fa-circle"></i> Unregistered</span>',
                    "Closed":       '<span class="badge-closed"><i class="fas fa-circle"></i> Closed</span>',
                    "Transferred":  '<span class="badge-transferred"><i class="fas fa-circle"></i> Transferred</span>',
                };
                let statusBadge = statusMap[r.operation_status] || '<span class="badge-active"><i class="fas fa-circle"></i> Active</span>';

                // Findings badge
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
                        <button class="tbl-btn tbl-btn-view" title="View & Print" onclick="viewInspection(${r.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="tbl-btn tbl-btn-edit" title="Edit" onclick="editInspection(${r.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="tbl-btn tbl-btn-delete" title="Delete" onclick="deleteInspection(${r.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
        }
        $("#inspectionTable").html(html);
    });
}

function loadBusinessOptions(){
    $.get("php/get/get_businesses.php", {}, function(data){
        let rows = JSON.parse(data);
        let html = '<option value="">— Select Business —</option>';
        rows.forEach(r => {
            html += `<option value="${r.id}"
                data-name="${r.business_name}"
                data-owner="${r.owner_name || ''}"
                data-barangay="${r.barangay || ''}">
                ${r.business_name}
            </option>`;
        });
        $("#selectBusiness").html(html);
    });
}

// Auto-fill owner and barangay on business select
$(document).on("change", "#selectBusiness", function(){
    let selected = $(this).find(":selected");
    let id = $(this).val();
    $("#inspection_business_id").val(id);
    $("#business_name").val(selected.data("name"));
    $("#owner_name").val(selected.data("owner"));
    $("#barangay").val(selected.data("barangay"));
});

function openAddModal(){
    $("#inspectionForm")[0].reset();
    $("#inspection_id").val("");
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