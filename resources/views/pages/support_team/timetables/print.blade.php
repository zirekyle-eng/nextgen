<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable - {{ $ttr->name }} - {{ $ttr->year }}</title>
    <style>
        :root {
            --navy: #0f2f66;
            --red: #c32033;
            --ink: #1b2d4d;
            --muted: #64799d;
            --line: #cfd9eb;
            --bg-soft: #f5f8ff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            color: var(--ink);
            background: #eef2f8;
            padding: 20px;
        }

        .sheet {
            max-width: 1280px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            box-shadow: 0 10px 24px rgba(15, 47, 102, .12);
            overflow: hidden;
        }

        .head {
            background: linear-gradient(120deg, var(--navy) 0%, #1b4f9d 60%, var(--red) 100%);
            color: #fff;
            padding: 16px 18px;
        }

        .head h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: .3px;
        }

        .head p {
            margin: 6px 0 0;
            font-size: 12px;
            opacity: .95;
        }

        .meta {
            display: grid;
            grid-template-columns: repeat(4, minmax(120px, 1fr));
            gap: 8px;
            margin-top: 10px;
        }

        .meta-item {
            border: 1px solid rgba(255,255,255,.3);
            border-radius: 8px;
            padding: 7px 9px;
            background: rgba(255,255,255,.12);
        }

        .meta-item .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .4px;
            opacity: .9;
            margin-bottom: 2px;
        }

        .meta-item .value {
            font-size: 12px;
            font-weight: 700;
            line-height: 1.3;
        }

        .table-wrap {
            padding: 14px;
            overflow-x: auto;
        }

        .tt {
            width: 100%;
            min-width: 960px;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid var(--line);
        }

        .tt th, .tt td {
            border: 1px solid var(--line);
            padding: 8px 7px;
            text-align: center;
            vertical-align: middle;
        }

        .tt thead th {
            background: var(--bg-soft);
            color: #2f4a77;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .35px;
        }

        .tt .time {
            width: 115px;
            background: #f9fbff;
            color: #173867;
            font-weight: 800;
            font-size: 11px;
        }

        .time small {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-weight: 600;
            font-size: 10px;
        }

        .subject {
            display: inline-block;
            max-width: 100%;
            background: #eaf1ff;
            color: #1f3f73;
            border: 1px solid #cedcf6;
            border-radius: 7px;
            padding: 4px 7px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.3;
            word-break: break-word;
        }

        .empty {
            color: #9dafcb;
            font-size: 12px;
            font-weight: 700;
        }

        .print-bar {
            padding: 0 14px 14px;
            display: flex;
            justify-content: center;
        }

        .print-btn {
            border: 0;
            border-radius: 8px;
            padding: 9px 14px;
            background: var(--navy);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .print-btn:hover {
            background: #0b2450;
        }

        @media print {
            @page {
                size: landscape;
                margin: 8mm;
            }

            body {
                background: #fff;
                padding: 0;
            }

            .sheet {
                max-width: none;
                border: 0;
                box-shadow: none;
                border-radius: 0;
            }

            .head {
                padding: 8mm 8mm 6mm;
            }

            .table-wrap {
                padding: 0 8mm 8mm;
            }

            .print-bar {
                display: none !important;
            }

            .tt {
                min-width: 100%;
            }

            .tt th, .tt td {
                padding: 6px 5px;
                font-size: 10px;
            }

            .subject {
                font-size: 10px;
                padding: 3px 5px;
            }
        }
    </style>
</head>
<body>
@php
    $tableType = $ttr->exam_id ? 'Exam Timetable' : 'Class Timetable';
@endphp
<div class="sheet">
    <div class="head">
        <h1>{{ strtoupper(config('app.name')) }} - TIMETABLE</h1>
        <p>{{ ucwords($s['address']) }} | {{ config('app.url') }}</p>
        <div class="meta">
            <div class="meta-item">
                <div class="label">Timetable</div>
                <div class="value">{{ $ttr->name }}</div>
            </div>
            <div class="meta-item">
                <div class="label">Class</div>
                <div class="value">{{ $my_class->name }}</div>
            </div>
            <div class="meta-item">
                <div class="label">Type</div>
                <div class="value">{{ $tableType }}</div>
            </div>
            <div class="meta-item">
                <div class="label">Year</div>
                <div class="value">{{ $ttr->year }}</div>
            </div>
        </div>
    </div>

    <div class="table-wrap">
        <table class="tt">
            <thead>
                <tr>
                    <th class="time">Time</th>
                    @foreach($days as $day)
                        <th>
                            @if($ttr->exam_id)
                                {{ date('l', strtotime($day)) }}<br>{{ date('d/m/Y', strtotime($day)) }}
                            @else
                                {{ $day }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($time_slots as $tms)
                    <tr>
                        <td class="time">
                            {{ $tms->time_from }}
                            <small>{{ $tms->time_to }}</small>
                        </td>
                        @foreach($days as $day)
                            @php
                                $subject = $d_time->where('day', $day)->where('time', $tms->full)->first();
                            @endphp
                            <td>
                                @if($subject && $subject['subject'])
                                    <span class="subject">{{ $subject['subject'] }}</span>
                                @else
                                    <span class="empty">-</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="print-bar">
        <button class="print-btn" type="button" onclick="window.print()">Print Timetable</button>
    </div>
</div>
</body>
</html>
