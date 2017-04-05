<?php

namespace Avem\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Avem\Http\Controllers\Controller;

use Avem\User;
use Avem\Charge;
use Avem\MbMember;
use Avem\MbMemberPeriod;

class MbMemberController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		return view('admin.mbMembers.index', [
			'mbMembers' => MbMember::all(),
			'charges'   => Charge::all(),
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		return view('admin.mbMembers.create', [
			'users' => User::all(),
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$user = User::findOrFail($request->input('user'));
		$user->mbMember()->create($request->except('user'));
		return redirect()->route('admin.mbMembers.index');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \Avem\MbMember  $mbMember
	 * @return \Illuminate\Http\Response
	 */
	public function show(MbMember $mbMember)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \Avem\MbMember  $mbMember
	 * @return \Illuminate\Http\Response
	 */
	public function edit(MbMember $mbMember)
	{
		return view('admin.mbMembers.edit', [
			'mbMember' => $mbMember,
			'users'    => User::all(),
		]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Avem\MbMember  $mbMember
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, MbMember $mbMember)
	{
		$mbMember->fill($request->all());
		$mbMember->user()->associate($request->input('user'));
		$mbMember->save();

		return redirect()->route('admin.mbMembers.index');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \Avem\MbMember  $mbMember
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(MbMember $mbMember)
	{
		$mbMember->delete();
		return redirect()->route('admin.mbMembers.index');
	}

	private function endAllActiveCharges(MbMember $mbMember)
	{
		$activePeriods = $mbMember->mbMemberPeriods()->active();
		$activePeriods->update([ 'end' => Carbon::now() ]);
	}

	private function createMbMemberPeriod(Request $request)
	{
		$now = Carbon::now();
		$charge = Charge::findOrFail($request->input('charge'));
		$period = new MbMemberPeriod([
			'start' => $now, 'end' => $this->nextPeriodDate($now),
		]);

		$period->charge()->associate($charge);
		return $period;
	}

	private function nextPeriodDate($date)
	{
		$until = Carbon::createFromDate(null, 10, 1);
		return $date < $until ? $until : $until->addYear();
	}

	/**
	 * Renew specified MB member for given charge.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Avem\MbMember  $mbMember
	 * @return \Illuminate\Http\Response
	 */
	public function renew(Request $request, MbMember $mbMember)
	{
		if ($mbMember->hasActiveCharge)
			$this->endAllActiveCharges($mbMember);

		if ($request->has('charge')) {
			$period = $this->createMbMemberPeriod($request);
			$period->mbMember()->associate($mbMember);
			$period->save();
		}

		return redirect()->route('admin.mbMembers.index');
	}
}
