@extends('layouts.app')
@section('title', 'Users & Customer — Admin PestaGo')
@section('content')
<script>window.location.href = "{{ route('admin.customers.index') }}";</script>
<div style="padding:40px;text-align:center;">
    <p>Mengalihkan ke <a href="{{ route('admin.customers.index') }}">Manajemen Customer</a>...</p>
</div>
@endsection
