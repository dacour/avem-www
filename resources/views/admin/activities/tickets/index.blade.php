@extends('admin.activities.modal')

@section('modal-content')
	<table class="table">
		<thead>
			<tr>
				<th>Expedido por</th>
				<th>Fecha de expedición</th>
				<th>Fecha de expiración</th>
				<th>Tickets canjeados</th>
				<th></th>
			</tr>
		</thead>

		<tbody>
			@forelse ($activity->ticketLots->sortBy('isExpired') as $ticketLot)
				<?php $lotTickets = Avem\ActivityTicket::fromTicketLot($ticketLot)->get() ?>
				<tr class="{{ $ticketLot->isExpired ? 'table-danger' : '' }}">
					<td>{{ $ticketLot->issuerPeriod->user->fullName }}</td>
					<td>{{ $ticketLot->created_at->diffForHumans()  }}</td>
					<td>{{ $ticketLot->expires_at->diffForHumans()  }}</td>
					<td>
						{{ $lotTickets->where('performed_activity_id', '!=', null)->count() }}
						/ {{ $lotTickets->count() }}
					</td>
					<td>
						@if (!$ticketLot->isExpired)
							<a target="_blank" role="button" class="my-1 btn btn-sm btn-block btn-secondary{{
								Gate::denies('view', $lotTickets->first()) ? ' disabled' : ''
							}}"
							   href="{{ route('admin.activities.tickets.show', [$activity, $lotTickets->first()]) }}">
								Imprimir
							</a>
							<a role="button" href="{{ route('admin.activities.tickets.expire', [$activity, $lotTickets->first()]) }}" class="btn btn-sm btn-block btn-danger{{
								Gate::denies('update', $lotTickets->first()) ? ' disabled' : ''
							}}">Expirar</a>
						@endif
					</td>
				</tr>
			@empty
				<tr>
					<td colspan="5" class="text-center">
						<i class="fa fa-times mr-1"></i>
						Todavía no se han generado tickets para esta actividad.
					</td>
				</tr>
			@endforelse
		</tbody>
	</table>

	<a href="{{ route('admin.activities.tickets.create', [$activity]) }}" role="button" class="btn btn-secondary btn-block{{
		Gate::denies('update', $activity) ? ' disabled' : ''
	}}">
		Generar tickets
	</a>
@endsection
