<style>
.box-shadow1{
  box-shadow: 0px 4px 20px -2px rgba(0,0,0,0.75)!important;
}
.card-header {
    border-bottom: 1px solid #4e5bf2;
}
</style>
<div class="row ">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-body">
        <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?> </h4>
      </div> <!-- end card body-->
    </div> <!-- end card -->
  </div><!-- end col-->
</div>
<div class="row">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-body">
        <h4 class="header-title mb-3"><?php echo get_phrase('choose_a_plan'); ?></h4>
        <div class="container">
              <div class="card-deck mb-3 text-center">
                <?php foreach ($plans as $key => $plan) { ?>
                  <div class="card mb-4 box-shadow box-shadow1">
                    <div class="card-header">
                      <h4 class="my-0 font-weight-normal"><?php echo strtoupper($plan['name'])?></h4>
                    </div>
                    <div class="card-body">
                      <h1 class="card-title pricing-card-title"><?php echo $plan['price']?> <small class="text-muted">/ reg</small></h1>
                      <ul class="list-unstyled mt-3 mb-4">
                        <li><strong><?php echo $plan['courses']?></strong> No of Courses</li>
                        <li><strong><?php echo $plan['classes']?></strong> No of Classes</li>
                        <li><strong><?php echo $plan['course_minutes']?></strong> Live session per course(minutes)</li>
                        <li><strong><?php echo $plan['students'] ?></strong> No of students per course</li>
                        <li><strong><?php echo $plan['cloud_space'] ?></strong> Cloud space per course</li>
                      </ul>
                      <form action="<?php echo site_url('institute/plan_price'); ?>" method="post">
                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>" />
                        <button class = "btn btn-primary" type="submit" name="button"><?php echo get_phrase('buy_now'); ?></button>
                      </form>
                      <!-- <a href = "javascript::" class="btn btn-lg btn-block btn-primary" id = "plan_<?php echo $plan['id']; ?>" onclick="handlePlanNow(this)"><?php echo get_phrase('buy_now'); ?></a> -->
                    </div>
                  </div>
                <?php } ?>
              </div>
            </div>
      </div>
    </div>
  </div>
</div>

<script>
function handlePlanNow(elem) {
  url1 = '<?php echo site_url('institute/handlePlanItemForPurchanseNowButton');?>';
  var explodedArray = elem.id.split("_");
  var plan_id = explodedArray[1];

  $.ajax({
    url: url1,
    type : 'POST',
    data : {plan_id : plan_id},
    success: function(response)
    {
    console.log(response);
    // debugger;
    },
    error : function(response) {
        console.log(response);
    }
  });
}
</script>
