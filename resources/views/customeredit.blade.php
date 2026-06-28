@extends('layouts.app')
@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>{{ __('customeredit.edit_customer_details', ['name' => $customer->name]) }}</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">{{ __('customeredit.home') }}</a></li>
          <li class="breadcrumb-item active">{{ __('customeredit.customer_edit') }}</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<section class="content">
  <form id="customer_edit_form" method="POST" action="{{$route}}">
    @method('PATCH')
    @csrf
    <div class="row">
      <div class="col-md-6">
        <div class="card card-primary">
          <div class="card-body" style="display: block;">

            <div class="form-group">
              <label for="customer_name">{{ __('customeredit.customer_name') }}</label>
              <input type="text" value="{{old('customer_name',$customer->name)}}" name="customer_name" id="customer_name" class="form-control">
              @if($errors->has('customer_name'))
                <label for="username" class="error"> {{ $errors->first('customer_name') }} </label>
              @endif  
            </div>

            <div class="form-group">
              <label for="inputName">{{ __('customeredit.customer_mobile') }}</label>
              <input type="number" value="{{$customer->mobile_number}}" name="customer_mobile" id="inputName" class="form-control">
            </div>

            <div>
              <div class="form-group">
                <label>{{ __('customeredit.users') }}</label>
                <select id='area-select' class="form-control select2" name="area" style="width: 100%;">
                  <option value="0">{{ __('customeredit.all') }}</option>
                  <?php echo $area; ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="inputDescription">{{ __('customeredit.address') }}</label>
              <textarea name="address" id="inputDescription" class="form-control" rows="4">{{$customer->address}}</textarea>
            </div>

            <div class="form-group">
              <label for="inputClientCompany">{{ __('customeredit.dob') }}</label>
              <input type="date" value="{{$customer->date_of_birth}}" name="dob" id="inputClientCompany" class="form-control">
            </div>

            <div class="form-group">
              <label for="inputProjectLeader">{{ __('customeredit.wedding_date') }}</label>
              <input type="date" value="{{$customer->wedding_date}}" name="dow" id="inputProjectLeader" class="form-control">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
        <div class="col-6">
          <a href="{{route('customer.index')}}" class="btn btn-danger">{{ __('customeredit.cancel') }}</a>
          <input type="submit" value="{{ __('customeredit.update') }}" class="btn btn-success float-right">
        </div>
      </div>
  </form>
</section>
<script>
$(document).ready(function () {
    $(document).on('click', '#customer_edit_form', function (e) {
        $("form#customer_edit_form").validate({
            rules: {
                // customer_name: { required: true},
                customer_mobile: { required: true},
                area:{required:true},
                address: { required: true},
            },
            messages: {
              // customer_name: { required: "Please enter customer name"},
              customer_mobile: { required: "{{ __('customeredit.validation_enter_mobile') }}"},
              area: { required: "{{ __('customeredit.validation_select_area') }}"},
              address: { required: "{{ __('customeredit.validation_enter_address') }}"},
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