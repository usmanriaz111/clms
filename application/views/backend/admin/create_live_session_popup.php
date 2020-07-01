<style>
   .flatpickr-wrapper{
  width: 100% !important;
}
   </style>

<form class="required-form" action="<?php echo site_url('admin/live_session/add'); ?>" method="post" enctype="multipart/form-data">
   <div class="row">
      <div class="col-xl-12">
         <div class="card">
            <div class="card-body">
               <h4 class="header-title mb-3"><?php echo get_phrase('create_live_session'); ?></h4>
               <div class="form-group">
                  <label for="name"><?php echo get_phrase('session_name'); ?></label>
                  <input class="form-control" type="text" name="session_name" id="session_name" required>
                  <input class="form-control d-none" type="number" name="course_id" value="<?php echo $course_id;?>">
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
                  <label for="classes"><?php echo get_phrase('select a country'); ?></label>
                  <select name="timezone_offset" id="timezone-offset" class="span5 select2 form-control"  data-toggle="select2">
                  <option value="-12:00">(GMT -12:00) Eniwetok, Kwajalein</option>
	               <option value="-11:00">(GMT -11:00) Midway Island, Samoa</option>
	               <option value="-10:00">(GMT -10:00) Hawaii</option>
	               <option value="-09:50">(GMT -9:30) Taiohae</option>
	               <option value="-09:00">(GMT -9:00) Alaska</option>
	               <option value="-08:00">(GMT -8:00) Pacific Time (US &amp; Canada)</option>
	               <option value="-07:00">(GMT -7:00) Mountain Time (US &amp; Canada)</option>
	               <option value="-06:00">(GMT -6:00) Central Time (US &amp; Canada), Mexico City</option>
	               <option value="-05:00">(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima</option>
	               <option value="-04:50">(GMT -4:30) Caracas</option>
	               <option value="-04:00">(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz</option>
	               <option value="-03:50">(GMT -3:30) Newfoundland</option>
	               <option value="-03:00">(GMT -3:00) Brazil, Buenos Aires, Georgetown</option>
	               <option value="-02:00">(GMT -2:00) Mid-Atlantic</option>
	               <option value="-01:00">(GMT -1:00) Azores, Cape Verde Islands</option>
	               <option value="+00:00">(GMT) Western Europe Time, London, Lisbon, Casablanca</option>
	               <option value="+01:00">(GMT +1:00) Brussels, Copenhagen, Madrid, Paris</option>
	               <option value="+02:00" selected="selected">(GMT +2:00) Kaliningrad, South Africa</option>
	               <option value="+03:00">(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg</option>
	               <option value="+03:50">(GMT +3:30) Tehran</option>
	               <option value="+04:00">(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>
	               <option value="+04:50">(GMT +4:30) Kabul</option>
	               <option value="+05:00">(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>
	               <option value="+05:50">(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>
	               <option value="+05:75">(GMT +5:45) Kathmandu, Pokhara</option>
	               <option value="+06:00">(GMT +6:00) Almaty, Dhaka, Colombo</option>
	               <option value="+06:50">(GMT +6:30) Yangon, Mandalay</option>
	               <option value="+07:00">(GMT +7:00) Bangkok, Hanoi, Jakarta</option>
	               <option value="+08:00">(GMT +8:00) Beijing, Perth, Singapore, Hong Kong</option>
	               <option value="+08:75">(GMT +8:45) Eucla</option>
	               <option value="+09:00">(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>
	               <option value="+09:50">(GMT +9:30) Adelaide, Darwin</option>
	               <option value="+10:00">(GMT +10:00) Eastern Australia, Guam, Vladivostok</option>
	               <option value="+10:50">(GMT +10:30) Lord Howe Island</option>
	               <option value="+11:00">(GMT +11:00) Magadan, Solomon Islands, New Caledonia</option>
	               <option value="+11:50">(GMT +11:30) Norfolk Island</option>
	               <option value="+12:00">(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka</option>
	               <option value="+12:75">(GMT +12:45) Chatham Islands</option>
	               <option value="+13:00">(GMT +13:00) Apia, Nukualofa</option>
	               <option value="+14:00">(GMT +14:00) Line Islands, Tokelau</option>
                  </select>
                  
               </div>
               <!-- <div class="form-group">
                  <label for="cloud_space"><?php echo get_phrase('live_session_time_in_mins'); ?></label>
                  <input class="form-control" type="number" name="time" id="time" required>
               </div> -->
               <div class="form-group">
                  <label for="datepicker" class="label-control" ><?php echo get_phrase('start_date_time'); ?></label><br/>
                  <input type="datetime-local" id="" name="start_session" class="form-control" required>
               </div>

               <div class="form-group">
                  <label for="datepicker" class="label-control" ><?php echo get_phrase('duration'); ?></label><br/>
                  <input type="number" name="duration"  min="15" class="form-control" placeholder="45 Minutes" required>
               </div>
              <div class="float-left">
              <button class="btn btn-secondary" data-dismiss="modal"><?php echo get_phrase("close"); ?></button>
              </div>
               <div class="text-right">
                  <button class = "btn btn-success" id="session_create" type="submit" name="button"><?php echo get_phrase('submit'); ?></button>
               </div>
            </div>
         </div>
      </div>
   </div>
</form>
<script type="text/javascript">

$('#scrollable-modal').on('show.bs.modal', function(e) {
   alert('here');
   document.getElementById("datepicker").flatpickr({
   //  enableTime: true,
    dateFormat: "Y-m-d H:i:s",
    altInput: true,
    minDate: dateToday,
   //  static: true,
});
document.getElementById("datepicker2").flatpickr({
   enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    time_24hr: true
});
})



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
   
</script>