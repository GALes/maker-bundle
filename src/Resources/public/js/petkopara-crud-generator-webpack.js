import $ from "jquery";

export function toggleAll($event)
{
    const $checkHeader = $('#check-all-thead');
    const $aInputs = $('.check-all-row');
    let checkStatus = $checkHeader.prop('checked');

    // console.log('toggleAll');

    $aInputs.each((index, element) => {
        // console.log(element);
        $(element).prop('checked', checkStatus);
    });

    $('#bulkSubmitBtn').prop('disabled', !checkStatus);
}

//Checks if at least one checkbox is selected.
export function bulkSubmitBtnManage()
{
    var $checkboxs = $('.check-all-row');
    var selected = false;

    $checkboxs.each((index, element) => {
        if ($(element).prop('checked')) {
            selected = true;
            return;
        }
    });

    $('#bulkSubmitBtn').prop('disabled', !selected);
}
