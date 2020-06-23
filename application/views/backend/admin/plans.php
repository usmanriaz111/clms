<style>
  .table td, .table th{
    padding: .95rem 0.5rem !important;
  }
  thead, tbody{
      text-align: center;
   }
</style>
<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                <a href = "<?php echo site_url('admin/plan_form/add_plan_form'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo get_phrase('add_plan'); ?></a>
            </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
              <h4 class="mb-3 header-title"><?php echo get_phrase('plans'); ?></h4>
              <div class="table-responsive-sm mt-4">
                <table id="basic-datatable" class="table table-striped table-centered mb-0">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th><?php echo get_phrase('name'); ?></th>
                      <th><?php echo get_phrase('price'); ?></th>
                      <th><?php echo get_phrase('no_of_courses'); ?></th>
                      <th><?php echo get_phrase('no_of_classes'); ?></th>
                      <th><?php echo get_phrase('no_of_students_per_class'); ?></th>
                      <th><?php echo get_phrase('no_of_live_minutes'); ?></th>
                      <th><?php echo get_phrase('cloud_space'); ?></th>
                      <th><?php echo get_phrase('Institute'); ?></th>
                      <th><?php echo get_phrase('private'); ?></th>
                      <th><?php echo get_phrase('actions'); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                      <?php
                       foreach ($plans as $key => $plan): ?>
                       <?php
                       $institute = '';
                       $institute = $this->db->get_where('purchased_plans', array('plan_id' => $plan['id']))->num_rows();
                       ?>
                        <tr>
                            <td><?php echo $key+1; ?></td>
                            <td><?php echo $plan['name']; ?></td>
                            <td><?php echo get_currency() .' '.$plan['price']; ?></td>
                            <td><?php echo $plan['courses']; ?></td>
                            <td><?php echo $plan['classes']; ?></td>
                            <td><?php echo $plan['students']; ?></td>
                            <td><?php echo $plan['course_minutes']; ?></td>
                            <td><?php echo $plan['cloud_space'].'GB'; ?></td>
                            <td><?php
                            echo $institute;
                            ?></td>
                            
                            <td><?php echo $plan['private']; ?></td>
                            <td>
                                  <div class="dropright dropright">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="<?php echo site_url('admin/plan_form/edit_plan_form/'.$plan['id']) ?>"><?php echo get_phrase('edit'); ?></a></li>
                                        <li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/plans/delete/'.$plan['id']); ?>');"><?php echo get_phrase('delete'); ?></a></li>
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
