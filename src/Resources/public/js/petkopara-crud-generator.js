function toggleAll(source) {
    // var source = document.getElementById('check-all-thead');

    console.log(source);
    var aInputs = document.getElementsByClassName("check-all-row");
    console.log(aInputs);
    for (var i = 0; i < aInputs.length; i++) {
        console.log(aInputs[i]);
        aInputs[i].checked = source.checked;
    }

    if (source.checked) {
        document.getElementById('bulkSubmitBtn').disabled = false;
    } else {
        document.getElementById('bulkSubmitBtn').disabled = true;
    }
}

//Checks if at least one checkbox is selected.
function bulkSubmitBtnManage()
{
    var checkboxs = document.getElementsByClassName("check-all");
    var selected = false;
    for (var i = 0, l = checkboxs.length; i < l; i++)
    {
        if (checkboxs[i].checked)
        {
            selected = true;
            break;
        }
    }
    
    if (selected) {
        document.getElementById('bulkSubmitBtn').disabled = false;
    } else {
        document.getElementById('bulkSubmitBtn').disabled = true;

    }
}

$(function() {

    $('#check-all-thead').on('click', function() {
        toggleAll(this)
    });
    $('.check-all-row').on('click', function () {
        bulkSubmitBtnManage();
    });

    $('#filters :input').change(function () {
        $('#button-export').attr('data-status','disabled');
    });

    $('#button-export').click('', function () {
        if ( $('#button-export').attr('data-status') == 'disabled' ) {
            $('#alert-container').append(
                `<div class="alert alert-danger">
                    Los filtros cambiaron, debe buscar primero antes de exportar
                </div>`
            );
            return false;
        }
        else {
            $('#loading').show();
            var interval = setInterval(function () {
                if ($.cookie('FileLoading')) {
                    $('#loading').hide();
                    $.removeCookie('FileLoading');
                    clearInterval(interval);
                }
            }, 1000);

            return true;
        }
    });

});