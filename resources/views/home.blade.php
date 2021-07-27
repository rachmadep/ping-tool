@extends ('layouts.app')

@section ('content')
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card">
                <div class="card-body">
                    <ping-component></ping-component>
                </div>
            </div>
            
            <ping-chart hostname="google.com"></ping-chart>
            {{-- <ping-chart hostname="mamikos.com"></ping-chart> --}}
        </div>
    </div>
@endsection