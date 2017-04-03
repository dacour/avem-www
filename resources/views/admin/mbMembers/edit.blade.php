@extends('layouts.admin')

@section('content')
	<h1 class="my-4">Editar miembro de junta</h1>
	<form method="post" action="{{ route('admin.mbMembers.update', [$mbMember]) }}">
		{{ csrf_field() }}
		{{ method_field('put') }}

		@include('admin.mbMembers.form', compact('users', 'mbMember'))

		<p class="my-4 text-center">
			<button class="btn btn-primary" type="submit">Guardar miembro</button>
		</p>
	</form>
@stop
