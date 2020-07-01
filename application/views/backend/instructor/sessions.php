<?php
 $live_sessions = $this->crud_model->fetch_instructor_events();
?>
<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
            </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="row">
<div id="calendar"></div>
</div>
<script>
    $(document).ready(function(){
        $('#calendar').fullCalendar({   //Removed function() from here
            firstDay: 6,
            timeFormat: 'HH:mm',
        eventAfterRender: function(event, element) {
            $(element).tooltip({
                title: event.description,
                container: "body"
            });
        },
        events:<?php echo $live_sessions?>
});
    });
        </script>