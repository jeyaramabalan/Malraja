<html>
<style>
.datatable {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
  font-size: 12px;
}
.datatable td, .datatable th {
  border: 1px solid #ddd;
  padding: 8px;
}
.datatable tr:nth-child(even){background-color: #f2f2f2;}
.datatable tr:hover {background-color: #ddd;}
.datatable th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #86c141;
  color: white;
}
</style>
<div style="width:1000px; margin:0 auto ;">
    <table class="datatable"  style="border-collapse: collapse; margin-top: 10px; color: #000; font-size: 14px; ">
    <thead style="background-color: green;">
        <tr>
        <th width="30" style="color: white;">#</th>
        <th width="60" style="color: white;">{{ __('pdf.name') }}</th>
        <th width="70" style="color: white;">{{ __('pdf.mobile') }}</th>
        <th width="280" style="color: white;">{{ __('pdf.address') }}</th>
        <th width="60" style="color: white;">{{ __('pdf.area') }}</th>
        <th width="50" style="color: white;">{{ __('pdf.dob') }}</th>
        <th width="50" style="color: white;">{{ __('pdf.wedding_date') }}</th>
        </tr>
    </thead>
    <tbody>
    @foreach($customers as $customer)
        <tr>
            <td>{{ $loop->iteration}}</td>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->mobile_number }}</td>
            <td align="center">{{ $customer->address }}</td>
            <td>{{ $customer->area_name }}</td>
            <td>{{ $customer->date_of_birth }}</td>
            <td>{{ $customer->wedding_date }}</td>
        </tr>
    @endforeach
    </tbody>
    </table>
</div>
</html>