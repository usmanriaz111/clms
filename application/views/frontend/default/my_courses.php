<?php

$my_courses = $this->user_model->my_courses()->result_array();

$live_sessions_arr = array();
foreach ($my_courses as $my_course) {
    array_push($live_sessions_arr, $my_course['course_id']);
}

$this->db->where_in('course_id', $live_sessions_arr);
$live_sessions = $this->db->get('live_sessions')->result_array();

$categories = array();
foreach ($my_courses as $my_course) {
    $course_details = $this->crud_model->get_course_by_id($my_course['course_id'])->row_array();
    if (!in_array($course_details['category_id'], $categories)) {
        array_push($categories, $course_details['category_id']);
    }
}
?>
<section class="page-header-area my-course-area">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="page-title"><?php echo get_phrase('my_courses'); ?></h1>
                <ul>
                  <li class="active"><a href="<?php echo site_url('home/my_courses'); ?>"><?php echo get_phrase('all_courses'); ?></a></li>
                  <li><a href="<?php echo site_url('home/my_wishlist'); ?>"><?php echo get_phrase('wishlists'); ?></a></li>
                  <li><a href="<?php echo site_url('home/my_messages'); ?>"><?php echo get_phrase('my_messages'); ?></a></li>
                  <li><a href="<?php echo site_url('home/purchase_history'); ?>"><?php echo get_phrase('purchase_history'); ?></a></li>
                  <li><a href="<?php echo site_url('home/profile/user_profile'); ?>"><?php echo get_phrase('user_profile'); ?></a></li>
                  <li><a href="<?php echo site_url('home/live_sessions'); ?>"><?php echo get_phrase('live_sessions'); ?></a></li>
                  <!-- <li><a href="<?php echo site_url('home/amazons3_setting_form/add_form'); ?>"><?php echo get_phrase('s3_settngs'); ?></a></li> -->
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="my-courses-area">
    <div class="container">
        <div class="row align-items-baseline">
            <div class="col-lg-6">
                <div class="my-course-filter-bar filter-box">
                    <span><?php echo get_phrase('filter_by'); ?></span>
                    <div class="btn-group">
                        <a class="btn btn-outline-secondary dropdown-toggle all-btn" href="#"data-toggle="dropdown">
                            <?php echo get_phrase('categories'); ?>
                        </a>

                        <div class="dropdown-menu">
                            <?php foreach ($categories as $category):
                                $category_details = $this->crud_model->get_categories($category)->row_array();
                                ?>
                                <a class="dropdown-item" href="#" id = "<?php echo $category; ?>" onclick="getCoursesByCategoryId(this.id)"><?php echo $category_details['name']; ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- <div class="btn-group">
                        <a class="btn btn-outline-secondary dropdown-toggle" href="#"data-toggle="dropdown">
                            <?php echo get_phrase('instructors'); ?>
                        </a>

                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#"><?php echo $instructor_details['first_name'].' '.$instructor_details['last_name']; ?></a>

                        </div>
                    </div> -->
                    <div class="btn-group">
                        <a href="<?php echo site_url('home/my_courses'); ?>" class="btn reset-btn" disabled><?php echo get_phrase('reset'); ?></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="my-course-search-bar">
                    <form action="">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="<?php echo get_phrase('search_my_courses'); ?>" onkeyup="getCoursesBySearchString(this.value)">
                            <div class="input-group-append">
                                <button class="btn" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="mt-4" id = "my_courses_area">
        <div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
              <h4 class="mb-3 header-title"><?php echo get_phrase('classes'); ?></h4>
              <!-- filter form here-->
              <div class="table-responsive-sm mt-4">
                <table id="basic-datatable" class="table table-striped table-centered mb-0">
                  <thead>
                    <tr>
                        <th><?php echo get_phrase('image'); ?></th>
                      <th><?php echo get_phrase('course_name'); ?></th>
                      <th><?php echo get_phrase('institute'); ?></th>
                      <th><?php echo get_phrase('instructor'); ?></th>
                      <th><?php echo get_phrase('actions'); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($my_courses as $my_course):
                    $course_details = $this->crud_model->get_course_by_id($my_course['course_id'])->row_array();
                    $instructor_details = $this->user_model->get_all_user($course_details['user_id'])->row_array();
                    $institute_details = $this->db->get_where('users', array('id' => $instructor_details['institute_id']))->row_array();
                    ?>
                    <tr>
                        <td>
                        <a href="<?php echo site_url('home/lesson/' . slugify($course_details['title']) . '/' . $my_course['course_id']); ?>">
                            <div class="course-image">
                                <img src="<?php echo $this->crud_model->get_course_thumbnail_url($my_course['course_id']); ?>" alt="" class="img-fluid" height="50" width="50">
                                <span class="play-btn"></span>
                            </div>
                        </a>
                        </td>
                        <td><?php echo ellipsis(get_phrase($course_details['title'])); ?></td>
                        <td><?php echo(get_phrase($institute_details['first_name'].' '.$institute_details['last_name']))?></td>
                        <td><?php echo(get_phrase($instructor_details['first_name'].' '.$instructor_details['last_name']))?></td>
                        <td>
                            <div class="dropright dropright">
                                <button type="button" class="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    ...
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo site_url('home/course/'.slugify($course_details['title']).'/'.$my_course['course_id']); ?>"><?php echo get_phrase('course_detail'); ?></a>
                                    </li>
                                    <li>
                                    <a class="dropdown-item" href="<?php echo site_url('home/lesson/'.slugify($course_details['title']).'/'.$my_course['course_id']); ?>"><?php echo get_phrase('start_lesson'); ?></a>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                  </tbody>
                </table>
              </div>
            </div>
        </div>
    </div>
        </div>
        </div>
    </div>
</section>


<script type="text/javascript">
function getCoursesByCategoryId(category_id) {
    $.ajax({
        type : 'POST',
        url : '<?php echo site_url('home/my_courses_by_category'); ?>',
        data : {category_id : category_id},
        success : function(response){
            $('#my_courses_area').html(response);
        }
    });
}

function getCoursesBySearchString(search_string) {
    $.ajax({
        type : 'POST',
        url : '<?php echo site_url('home/my_courses_by_search_string'); ?>',
        data : {search_string : search_string},
        success : function(response){
            $('#my_courses_area').html(response);
        }
    });
}

function getCourseDetailsForRatingModal(course_id) {
    $.ajax({
        type : 'POST',
        url : '<?php echo site_url('home/get_course_details'); ?>',
        data : {course_id : course_id},
        success : function(response){
            $('#course_title_1').append(response);
            $('#course_title_2').append(response);
            $('#course_thumbnail_1').attr('src', "<?php echo base_url().'uploads/thumbnails/course_thumbnails/';?>"+course_id+".jpg");
            $('#course_thumbnail_2').attr('src', "<?php echo base_url().'uploads/thumbnails/course_thumbnails/';?>"+course_id+".jpg");
            $('#course_id_for_rating').val(course_id);
            // $('#instructor_details').text(course_id);
            console.log(response);
        }
    });
}
</script>
