<?php

$my_courses = $this->user_model->my_courses()->result_array();

$live_sessions_arr = array();
foreach ($my_courses as $my_course) {
    array_push($live_sessions_arr, $my_course['course_id']);
}

$this->db->where_in('course_id', $live_sessions_arr);
$event_data = $this->db->get('live_sessions')->result_array();
foreach($event_data as $row)
{
    
    $start_date = $row['start_time'] + $row["timezone"][0] + $row["timezone"]*60*60;
    $start_time = gmdate('h:i A', $start_date);
    $start_date = gmdate('Y-m-d h:i:s', $start_date);
    $end_date = $row['end_time'] + $row["timezone"][0] + $row["timezone"]*60*60;
    $end_date = gmdate('Y-m-d h:i:s', $end_date);
    $cls = $this->db->get_where('classes', array('id' => $row['class_id']))->row_array();
    $course = $this->db->get_where('course', array('id' => $cls['course_id']))->row_array();
    $instructor = $this->db->get_where('users', array('id' => $course['user_id']))->row_array();
    $institute = $this->db->get_where('users', array('id' => $instructor['institute_id']))->row_array();

        $data[] = array(
        'id' => $row['id'],
        'title' => $row['name'],
        'description' => $start_time.'-Duration ('.ucfirst($institute['first_name']).' '.ucfirst($institute['last_name']).', '.ucfirst($course['title']).', '. ucfirst($cls['name']),
        'start' => $start_date,
        'end' => $end_date
        );
}
 $live_sessions = json_encode($data);
?>

<section class="page-header-area my-course-area">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="page-title"><?php echo get_phrase('my_courses'); ?></h1>
                <ul>
                  <li><a href="<?php echo site_url('home/my_courses'); ?>"><?php echo get_phrase('all_courses'); ?></a></li>
                  <li><a href="<?php echo site_url('home/my_wishlist'); ?>"><?php echo get_phrase('wishlists'); ?></a></li>
                  <li><a href="<?php echo site_url('home/my_messages'); ?>"><?php echo get_phrase('my_messages'); ?></a></li>
                  <li><a href="<?php echo site_url('home/purchase_history'); ?>"><?php echo get_phrase('purchase_history'); ?></a></li>
                  <li><a href="<?php echo site_url('home/profile/user_profile'); ?>"><?php echo get_phrase('user_profile'); ?></a></li>
                  <li class="active"><a href="<?php echo site_url('home/live_sessions'); ?>"><?php echo get_phrase('live_sessions'); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<div class="container">
<div class="row">
<div id="calendar"></div>
</div>
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