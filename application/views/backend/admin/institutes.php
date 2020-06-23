<style>
  .table td, .table th{
    padding: .95rem 0.5rem !important;
  }
</style>
<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php

use phpDocumentor\Reflection\Types\Null_;

echo $page_title; ?>
                <a href = "<?php echo site_url('admin/institute_form/add_insttitue_form'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo get_phrase('add_institute'); ?></a>
            </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
              <h4 class="mb-3 header-title"><?php echo get_phrase('intitutes'); ?></h4>
              <div class="table-responsive-sm mt-4">
                <table id="basic-datatable" class="table table-striped table-centered mb-0">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th><?php echo get_phrase('photo'); ?></th>
                      <th><?php echo get_phrase('name'); ?></th>
                      <th><?php echo get_phrase('plan_name'); ?></th>
                      <th><?php echo get_phrase('live_minutes'); ?></th>
                      <th><?php echo get_phrase('cloud_space'); ?></th>
                      <th><?php echo get_phrase('no_of_classes'); ?></th>
                      <th><?php echo get_phrase('no_of_students'); ?></th>
                      <th><?php echo get_phrase('no_of_courses'); ?></th>
                      <!-- <th><?php echo get_phrase('no_of_instructor'); ?></th> -->
                      <th><?php echo get_phrase('actions'); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                      <?php
                       foreach ($institutes as $key => $user): ?>
                        <tr>
                          <?php
                          $plan = $this->crud_model->check_plan($user['id'])->row_array();
                          if($plan['remaining_cloud_space'])
                          {
                            $cloud_space = $plan['remaining_cloud_space'] /1024;
                            $cloud_space = $cloud_space/1024;
                          }
                          ?>
                            <td><?php echo $key+1; ?></td>
                            <td><img src="<?php echo $this->user_model->get_user_image_url($user['id']);?>" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail"></td>
                            <td><?php echo $user['first_name'].' '.$user['last_name']; ?></td>
                            <td><?php echo $plan['name']; ?></td>
                            <td><?php echo $plan['remaining_minutes']; ?></td>
                            <td><?php 
                            if($cloud_space > 0){
                              echo round($cloud_space, 2) .'GB'; 
                              $cloud_space = 0;
                            }else{
                              echo '0 GB'; 
                            }
                            
                            ?></td>
                            <td><?php echo $plan['classes']; ?></td>
                            <td><?php echo $plan['students']; ?></td>
                            <td>
                                <!-- <?php echo $this->user_model->get_number_of_active_courses_of_instructor($user['id']).' '.strtolower(get_phrase('active_courses')); ?> -->
                                <?php echo $plan['courses']; ?>
                            </td>
                            <!-- <td>
                                <?php echo $this->user_model->get_number_of_instructor($user['id']).' '.strtolower(get_phrase('instructors')); ?>
                            </td> -->
                            <td>
                                <div class="dropright dropright">
                                  <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                      <i class="mdi mdi-dots-vertical"></i>
                                  </button>
                                  <ul class="dropdown-menu">
                                      <li><a class="dropdown-item" href="<?php echo site_url('admin/courses?category_id=all&status=all&institute_id='.$user['id'].'&price=all') ?>"><?php echo get_phrase('view_courses'); ?></a></li>
                                      <li><a class="dropdown-item" href="<?php echo site_url('admin/get_plans?institute_id='.$user['id']) ?>"><?php echo get_phrase('assign_plan'); ?></a></li>
                                      <li><a class="dropdown-item" href="<?php echo site_url('admin/institute_form/edit_institute_form/'.$user['id']) ?>"><?php echo get_phrase('edit'); ?></a></li>
                                      <li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/institutes/delete/'.$user['id']); ?>');"><?php echo get_phrase('delete'); ?></a></li>
                                  </ul>
                              </div>
                            </td>
                        </tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>
              </div>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>
