// Load settings on page load
$(document).ready(function(){
    loadSettings();
});

function loadSettings(){
    $.get("php/get/get_settings.php", function(res){
        let data = JSON.parse(res);
        $("#municipality").val(data.municipality);
        $("#province").val(data.province);

        if(data.logo){
            let src = "uploads/" + data.logo + "?t=" + new Date().getTime();
            $("#logoPreview").attr("src", src).show();
            $("#uploadPlaceholder").hide();
        }
    });
}

// Logo preview on file select
$("#logoInput").on("change", function(){
    let file = this.files[0];
    if(!file) return;

    if(!file.type.startsWith("image/")){
        alert("Please select an image file.");
        return;
    }

    let reader = new FileReader();
    reader.onload = function(e){
        $("#logoPreview").attr("src", e.target.result).show();
        $("#uploadPlaceholder").hide();
    };
    reader.readAsDataURL(file);
});

// Save system info
function saveSystem(){
    let formData = new FormData();
    formData.append("municipality", $("#municipality").val());
    formData.append("province",     $("#province").val());

    let file = $("#logoInput")[0].files[0];
    if(file) formData.append("logo", file);

    $.ajax({
        url: "php/update/update_system.php",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(res){
            showMsg("systemMsg", "System information saved successfully!", "success");
            setTimeout(() => location.reload(), 1500);
        },
        error: function(){
            showMsg("systemMsg", "Something went wrong. Please try again.", "error");
        }
    });
}

// Update password
function updatePassword(){
    let current = $("#current_password").val();
    let newPass = $("#new_password").val();
    let confirm = $("#confirm_password").val();

    if(!current || !newPass || !confirm){
        showMsg("passwordMsg", "Please fill in all password fields.", "error");
        return;
    }

    if(newPass !== confirm){
        showMsg("passwordMsg", "New passwords do not match.", "error");
        return;
    }

    if(newPass.length < 6){
        showMsg("passwordMsg", "Password must be at least 6 characters.", "error");
        return;
    }

    $.post("php/update/update_password.php", {
        current_password:  current,
        new_password:      newPass,
        confirm_password:  confirm
    }, function(res){
        if(res.includes("success") || res.includes("updated") || res.includes("changed")){
            showMsg("passwordMsg", "Password updated successfully!", "success");
            $("#current_password, #new_password, #confirm_password").val("");
        } else {
            showMsg("passwordMsg", res || "Failed to update password.", "error");
        }
    });
}