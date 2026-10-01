@extends('layouts.master')
@section('page_title', 'Class Recordings')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h5 class="card-title">Class Recordings</h5>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Recording ID</th>
                    <th>Meeting Name</th>
                    <th>Duration</th>
                    <th>Created At</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recordings as $rec)
                    <tr>
                        <td>
                            <code class="text-danger">{{ substr($rec->getRecordId(), 0, 15) }}...</code>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $rec->getName() }}</span>
                        </td>
                        <td>
                            {{ floor($rec->getDurationInMinutes()) }} دقيقة
                        </td>
                        <td>
                            @php
                                $date = new \DateTime('@' . ($rec->getStartTime() / 1000));
                            @endphp
                            {{ $date->format('Y-m-d H:i') }}
                        </td>
                        <td class="text-center">
                            @if($rec->getPlaybackLink())
                                <a href="{{ $rec->getPlaybackLink() }}" target="_blank" class="btn btn-sm btn-success" title="تشغيل">
                                    <i class="icon-play"></i> Play
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="icon-inbox"></i> {{ __('msg.no_recordings_available') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
