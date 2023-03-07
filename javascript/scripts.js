document.addEventListener('DOMContentLoaded', function () {
    if(window.location.pathname == '/backup/import.php') {
        var classes = document.querySelector('body').getAttribute('class');
        var this_c_id_partial = classes.split('course-')[1];
        var this_c_id = this_c_id_partial.split(" ")[0];
        if (this_c_id) {
            document.querySelector('#page-backup-import .import-course-selector .ics-existing-course .cell.c0 input[value="'+this_c_id+'"]').setAttribute('disabled','disabled');
        }
        document.querySelector("td.header.c0").outerHTML = document.querySelector("td.header.c0").outerHTML.replace(/td/g,"th")
    }
}, false);