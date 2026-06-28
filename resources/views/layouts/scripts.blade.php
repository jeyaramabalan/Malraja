<script>
    $('.select2').select2({
      theme: 'bootstrap4'
    });

    $("#e5").select2({
      tags: true,
      theme: 'bootstrap4'
    });

    $(".export-table").DataTable({
      "responsive": true,
      "lengthChange": false,
      "autoWidth": false,
      "searching": false,
      "sScrollX": '100%',
      // "buttons": ["excel", "pdf"]
    });
    //.buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

    $('.dt-buttons').addClass('mb-2');

    //Date picker
    $('#reservationdate').datetimepicker({
      format: 'L'
    });
    //Date range picker
    $('#datetimepicker').daterangepicker()
    
    toastr.options = {
            "closeButton": true,
            "debug": false,
            "positionClass": "toast-bottom-right",
            "onclick": null,
            "fadeIn": 300,
            "fadeOut": 1000,
            "timeOut": 5000,
            "extendedTimeOut": 1000
        }
        if ($('#status_span').length) {
            var status = $('#status_span').attr('data-status');
            if (status === '1') { //success
                toastr.success($('#status_span').attr('data-msg'));
            } else if (status === '0') { //error
                toastr.error($('#status_span').attr('data-msg'));
            }
        }

  </script>