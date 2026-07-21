<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Consultation Report</title>

    <style>
        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:12px;
            color:#222;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th,
        td{
            border:1px solid #000;
            padding:6px;
            vertical-align:top;
        }

        .no-border td{
            border:none;
        }

        .title{
            font-size:18px;
            font-weight:bold;
            text-align:center;
        }

        .subtitle{
            text-align:center;
            font-size:11px;
        }

        .section{
            background:#d9d9d9;
            font-weight:bold;
        }

        .label{
            width:150px;
            font-weight:bold;
            background:#f5f5f5;
        }

        .content{
            min-height:80px;
            white-space:pre-wrap;
        }

        .signature{
            text-align:center;
            padding-top:50px;
        }

    </style>

</head>

<body>

<table class="no-border">

    <tr>

        <td style="width:90px;text-align:center;">
            <!-- Hospital Logo -->
        </td>

        <td>

            <div class="title">
                EASTERN VISAYAS MEDICAL CENTER
            </div>

            <!-- <div class="subtitle">
                Hospital Information System
            </div>

            <div class="subtitle">
                CONSULTATION REPORT
            </div> -->

        </td>

    </tr>

</table>

<br>
<table>

    <tr class="section">
        <td colspan="6">
            PATIENT INFORMATION
        </td>
    </tr>

    <tr>
        <td class="label">Patient Name</td>
        <td colspan="5">
            {{ $consultation->patient?->last_name }},
            {{ $consultation->patient?->first_name }}
            {{ $consultation->patient?->middle_name }}
            {{ $consultation->patient?->extension_name }}
        </td>
    </tr>

    <tr>
        <td class="label">Address</td>
        <td colspan="5">
            {{ $consultation->patient?->address }}
        </td>
    </tr>

    <tr>
        <td class="label">Birth Date</td>
        <td>
            {{ optional($consultation->patient?->birthday)->format('M d, Y') }}
        </td>

        <td class="label">Age</td>
        <td>
            {{ \Carbon\Carbon::parse($consultation->patient?->birthdate)->age }}
        </td>

        <td class="label">Gender</td>
        <td>
            {{ $consultation->patient?->sex }}
        </td>
    </tr>

    <tr>
        <td class="label">Civil Status</td>
        <td>
            {{ $consultation->patient?->civil_status }}
        </td>

        <td class="label">Consultation Date</td>
        <td colspan="3">
            {{ optional($consultation->consultation_completed_at)->format('M d, Y h:i A') }}
        </td>
    </tr>

</table>

<br>

<table>

    <tr class="section">
        <td>
            CHIEF COMPLAINT
        </td>
    </tr>

    <tr>
        <td class="content">
            {{ $consultation->chief_complaint }}
        </td>
    </tr>

</table>

<br>

<table>

    <tr class="section">
        <td>
            SUBJECTIVE
        </td>
    </tr>

    <tr>
        <td class="content">
            {{ $consultation->subjective }}
        </td>
    </tr>

</table>

<br>

<table>

    <tr class="section">
        <td>
            OBJECTIVE
        </td>
    </tr>

    <tr>
        <td class="content">
            {{ $consultation->objective }}
        </td>
    </tr>

</table>

<br>

<table>

    <tr class="section">
        <td>
            ASSESSMENT
        </td>
    </tr>

    <tr>
        <td class="content">
            {{ $consultation->assessment }}
        </td>
    </tr>

</table>

<br>

<table>

    <tr class="section">
        <td>
            PLAN
        </td>
    </tr>

    <tr>
        <td class="content">
            {{ $consultation->plan }}
        </td>
    </tr>

</table>

<br>

<table>

    <tr>

        <td style="width:50%;border:none;"></td>

        <td style="border:none;">

            <div class="signature">

                ___________________________________<br>

                <strong>{{ $consultation->doctor?->name }}</strong><br>

                Attending Physician

            </div>

        </td>

    </tr>

</table>

</body>
</html>