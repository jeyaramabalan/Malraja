@extends('layouts.app')
@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>{{ __('visits.edit_visit') }}</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">{{ __('visits.home') }}</a></li>
          <li class="breadcrumb-item active">{{ __('visits.visit_edit') }}</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<section class="content">
  <form id="visit_edit_form" method="POST" action="{{$route}}">
    @method('PATCH')
    @csrf
    <div class="row">
      <div class="col-md-6">
        <div class="card card-primary">
          <div class="card-body" style="display: block;">

            <div>
              <div class="form-group">
                <label>{{ __('visits.purpose_of_visits') }}</label>
                <select class="form-control select2" name="purpose" style="width: 100%;">
                  <option value="">{{ __('visits.none') }}</option>
                  <?php echo $purpose_options; ?>
                </select>
              </div>
            </div>

            <div>
              <div class="form-group">
                <label>{{ __('visits.customer') }}</label>
                <select class="form-control select2" name="customer" style="width: 100%;">
                  <option value="">{{ __('visits.none') }}</option>
                  <?php echo $customer_option; ?>
                </select>
              </div>
            </div>

            <div>
              <div class="form-group">
                <label>{{ __('visits.accompanied_by') }}</label>
                <select class="form-control select2" name="accompained[]" style="width: 100%;" multiple>
                  <option value="">{{ __('visits.none') }}</option>
                  <?php echo $accompanied_option; ?>
                </select>
              </div>
            </div>

            <div>
              <div class="form-group">
                <label>{{ __('visits.products') }}</label>
                <select class="form-control select2" name="product[]" style="width: 100%;" multiple>
                  <option value="">{{ __('visits.none') }}</option>
                  <?php echo $product_option; ?>
                </select>
              </div>
            </div>

            <div>
              <div class="form-group">
                <label>{{ __('visits.follow_up_needed') }}</label>
                <select  class="form-control select2" name="follow" style="width: 100%;">
                  <option value="">{{ __('visits.select') }}</option>
                  <?php echo $follow_up_option; ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="quantity">{{ __('visits.quantity') }}</label>
              <input type="text" value="{{old('quantity',$visit->quantity)}}" name="quantity" class="form-control">
            </div>

            <div class="form-group">
              <label for="remark">{{ __('visits.remarks') }}</label>
              <input type="text" value="{{$visit->remarks}}" name="remark" class="form-control">
            </div>

            <div class="form-group">
              <label for="campaign">{{ __('visits.campaign_name') }}</label>
              <input type="text" value="{{$visit->campaign_name}}" name="campaign" class="form-control">
            </div>

          </div>
        </div>
      </div>
    </div>
    <div class="row">
        <div class="col-6">
          <a href="{{route('visits.index')}}" class="btn btn-danger">{{ __('visits.cancel') }}</a>
          <input type="submit" value="{{ __('visits.update') }}" class="btn btn-success float-right">
        </div>
      </div>
  </form>
</section>
<script>
$(document).ready(function () {
    $(document).on('click', '#visit_edit_form', function (e) {
        $("form#visit_edit_form").validate({
            rules: {
              purpose: { required: true},
              customer: { required: true},
              product:{required:true},
              follow: { required: true},
            },
            messages: {
              purpose: { required: "{{ __('visits.validation_select_purpose') }}"},
              customer: { required: "{{ __('visits.validation_select_customer') }}"},
              product: { required: "{{ __('visits.validation_select_product') }}"},
              follow: { required: "{{ __('visits.validation_select_follow_up') }}"},
            },
            focusInvalid: true,
            invalidHandler: function () {
                $(this).find(":input.error:first").focus();
            }
        });
    });
});
</script>
@endsection