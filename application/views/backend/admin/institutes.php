
<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
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
                      <th><?php echo get_phrase('email'); ?></th>
                      <th><?php echo get_phrase('no_of_active_courses'); ?></th>
                      <th><?php echo get_phrase('no_of_classes'); ?></th>
                      <th><?php echo get_phrase('no_of_students'); ?></th>
                      <th><?php echo get_phrase('no_of_instructor'); ?></th>
                      <th><?php echo get_phrase('plan_name'); ?></th>
                      <th><?php echo get_phrase('actions'); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                      <?php
                       foreach ($institutes as $key => $user): ?>
                       <?php
                       $plan = $this->crud_model->get_plan_by_id($user['plan_id']);
                       ?>
                        <tr>
                            <td><?php echo $key+1; ?></td>
                            <td>
                                <img src="<?php echo $this->user_model->get_user_image_url($user['id']);?>" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail">
                            </td>
                            <td><?php echo $user['first_name'].' '.$user['last_name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td>
                                <?php echo $this->user_model->get_number_of_active_courses_of_instructor($user['id']).' '.strtolower(get_phrase('active_courses')); ?>
                            </td>
                            <td>classes</td>
                            <td>students</td>
                            <td>
                                <?php echo $this->user_model->get_number_of_instructor($user['id']).' '.strtolower(get_phrase('instructors')); ?>
                            </td>
                            <td><?php echo $plan['name']; ?></td>
                            <td>
                                <div class="dropright dropright">
                                  <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                      <i class="mdi mdi-dots-vertical"></i>
                                  </button>
                                  <ul class="dropdown-menu">
                                      <li><a class="dropdown-item" href="<?php echo site_url('admin/courses?category_id=all&status=all&institute_id='.$user['id'].'&price=all') ?>"><?php echo get_phrase('view_courses'); ?></a></li>
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
