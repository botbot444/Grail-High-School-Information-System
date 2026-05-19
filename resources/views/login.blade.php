@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="text-center">Welcme to Grail</h3>
                    <form>
                        <div class="mb-3">
                            <label>Email Address</label>
                            <input type="email" class="form-control">
                        </div>
                        <button class="btn btn-primary w-100">Sign In</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
