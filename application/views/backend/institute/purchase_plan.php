<style>
.box-shadow1{
  box-shadow: 0px 4px 20px -2px rgba(0,0,0,0.75)!important;
}
.card-header {
    border-bottom: 1px solid #4e5bf2;
}
section.pricing {
  background: #007bff;
  background: linear-gradient(to right, #f7f7f7, #f3f5f7);
}

.pricing .card {
  border: none;
  border-radius: 1rem;
  transition: all 0.2s;
  box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
}

.pricing hr {
  margin: 1.5rem 0;
}

.pricing .card-title {
  margin: 0.5rem 0;
  font-size: 0.9rem;
  letter-spacing: .1rem;
  font-weight: bold;

}

.pricing .card-price {
  font-size: 3rem;
  margin: 0;
}

.pricing .card-price .period {
  font-size: 0.8rem;
}

.pricing ul li {
  margin-bottom: 1rem;
}

.pricing .text-muted {
  opacity: 0.7;
}

.pricing .btn {
  font-size: 80%;
  border-radius: 5rem;
  letter-spacing: .1rem;
  font-weight: bold;
  padding: 1rem;
  opacity: 1;
  transition: all 0.2s;
}
.fa-clock{
color: #4e5bf2 !important;
}

/* Hover Effects on Card */

@media (min-width: 992px) {
  .pricing .card:hover {
    margin-top: -.25rem;
    margin-bottom: .25rem;
    box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.3);
  }
  .pricing .card:hover .btn {
    opacity: 1;
  }
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
        <section class="pricing py-2">
  <div class="container">
    <div class="row">
    <?php foreach ($plans as $key => $plan) { ?>
      <div class="col-lg-4">
        <div class="card mb-5 mt-2 mb-lg-0">
          <div class="card-body">
          <?php if($this->user_model->get_current_user_plan() == $plan['id']){ ?>
              <span class="text-success">Active</span>
          <?php }?>
            <h5 class="card-title text-uppercase text-center"><?php echo strtoupper($plan['name'])?></h5>
            <h6 class="card-price text-center"><?php echo get_currency(); ?> <?php echo number_format((float)$plan['price'], 2, '.', '')?></h6>
            <h5 class="period text-center">Per Month</h5>
            <hr>
            <ul class="fa-ul">
              <li><span class="fa-li"><i class="fas fa-clock"></i></span><?php echo $plan['courses']?> Courses</li>
              <li class=""><span class="fa-li"><i class="fas fa-clock"></i></span><?php echo $plan['cloud_space'] ?> Cloud / Course</li>
              <li><span class="fa-li"><i class="fas fa-clock"></i></span><?php echo $plan['classes']?> Classes / Course</li>
              <li><span class="fa-li"><i class="fas fa-clock"></i></span><?php echo $plan['students'] ?> students / Class</li>
              <li><span class="fa-li"><i class="fas fa-clock"></i></span><?php echo $plan['course_minutes']?> Live / Plan</li>
            </ul>
            <form action="<?php echo site_url('institute/plan_price'); ?>" method="post">
                <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>" />
                <button class = "btn btn-block btn-primary text-uppercase" type="submit" name="button" ><?php echo get_phrase('GET STARRED'); ?></button>
            </form>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
</section>
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
