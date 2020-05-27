
<form class="required-form" action="<?php echo site_url('admin/live_session/add'); ?>" method="post" enctype="multipart/form-data">
   <div class="row">
      <div class="col-xl-12">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title mb-3"><?php echo get_phrase('create_live_session'); ?></h4>
               <div class="form-group">
                  <label for="name"><?php echo get_phrase('session_name'); ?></label>
                  <input class="form-control" type="text" name="session_name" id="session_name" required>
               </div>
               <div class="form-group">
                  <label for="classes"><?php echo get_phrase('select a class'); ?></label>
                  <select class="form-control select2" data-toggle="select2" name="live_session_class" id="live_session_class" required>
                     <?php foreach ( $classes as $class): ?>
                     <option value="<?php echo $class['id']; ?>"><?php echo $class['name']; ?></option>
                     <?php endforeach; ?>
                  </select>
               </div>
               <div class="form-group">
                  <label for="cloud_space"><?php echo get_phrase('live_session_time_in_mins'); ?></label>
                  <input class="form-control" type="number" name="time" id="time" required>
               </div>
               <div class="form-group">
                  <label for="datepicker" class="label-control" ><?php echo get_phrase('start_time'); ?></label><br/>
                  <input type="text" id="datepicker" name="start_session" class="form-control" required>
               </div>
               <div class="form-group">
                  <label for="datepicker2"><?php echo get_phrase('end_time'); ?></label><br/>
                  <input type="text" id="datepicker2" name="end_session" class="form-control" required>
               </div>
               <div class="text-center">
                  <button class = "btn btn-success" id="session_create" type="submit" name="button"><?php echo get_phrase('submit'); ?></button>
               </div>
            </div>
         </div>
      </div>
   </div>
</form>

<script>
   var dateToday = new Date();
    $(document).ready(function() {
        initTimepicker();
        $('.preload').hide();
        $('#session_create').click(function(){
            if($('#session_name').val() != '' && $('#time').val() !='' && $('#datepicker') != ''){
            $(".preload").fadeIn(1000, function() {});
            }
    });
    });
   document.getElementById("datepicker").flatpickr({
    enableTime: true,
    dateFormat: "Y-m-d H:i:s",
    altInput: true,
    minDate: dateToday,
    static: true,
});
document.getElementById("datepicker2").flatpickr({
    enableTime: true,
    dateFormat: "Y-m-d H:i:s",
    altInput: true,
    minDate: dateToday,
    static: true,
});
</script>