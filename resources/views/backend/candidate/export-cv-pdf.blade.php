<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Candidate CV Export</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
        }

        .cv-sheet {
            border: 1px solid #222;
            margin-bottom: 24px;
        }

        .header {
            text-align: center;
            padding: 10px 8px 6px;
            border-bottom: 1px solid #222;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            letter-spacing: 1px;
        }

        .header h2 {
            margin: 8px 0 0;
            font-size: 20px;
            display: inline-block;
            padding: 4px 12px;
            border: 1px solid #222;
            background: #d4f28b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #222;
            padding: 4px 6px;
            vertical-align: top;
        }

        .section {
            background: #f0f0f0;
            font-weight: 700;
            text-align: center;
        }

        .label {
            width: 25%;
            font-weight: 600;
            background: #fafafa;
        }

        .value {
            width: 25%;
        }

        .signature-row td {
            height: 36px;
            vertical-align: bottom;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @foreach ($candidates as $candidate)
        <div class="cv-sheet">
            <div class="header">
                <h1>{{ config('app.name') }}</h1>
                <h2>CURRICULUM VITAE</h2>
            </div>

            <table>
                <tr>
                    <th class="section" colspan="4">Personal Information</th>
                </tr>
                <tr>
                    <td class="label">Name</td>
                    <td class="value">{{ $candidate->user->name ?? '' }}</td>
                    <td class="label">Father's Name</td>
                    <td class="value">{{ $candidate->father_name ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">Date of Birth</td>
                    <td class="value">{{ $candidate->birth_date ? date('Y-m-d', strtotime($candidate->birth_date)) : '' }}</td>
                    <td class="label">Nationality</td>
                    <td class="value">{{ $candidate->nationality ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">Religion</td>
                    <td class="value">{{ $candidate->religion ?? '' }}</td>
                    <td class="label">Marital Status</td>
                    <td class="value">{{ $candidate->marital_status ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">Present Address</td>
                    <td class="value" colspan="3">
                        {{ $candidate->address ?? '' }}{{ $candidate->district ? ', '.$candidate->district : '' }}{{ $candidate->postcode ? ' - '.$candidate->postcode : '' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Permanent Address</td>
                    <td class="value" colspan="3">{{ $candidate->permanent_address ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">Contact No</td>
                    <td class="value">{{ $candidate->whatsapp_number ?? '' }}</td>
                    <td class="label">Email</td>
                    <td class="value">{{ $candidate->user->email ?? '' }}</td>
                </tr>

                <tr>
                    <th class="section" colspan="4">Passport Information</th>
                </tr>
                <tr>
                    <td class="label">Passport No</td>
                    <td class="value">{{ $candidate->passport_no ?? '' }}</td>
                    <td class="label">Place of Issue</td>
                    <td class="value">{{ $candidate->passport_place_of_issue ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">Issue Date</td>
                    <td class="value">{{ $candidate->passport_issue_date ? date('Y-m-d', strtotime($candidate->passport_issue_date)) : '' }}</td>
                    <td class="label">Expiry Date</td>
                    <td class="value">{{ $candidate->passport_expiry_date ? date('Y-m-d', strtotime($candidate->passport_expiry_date)) : '' }}</td>
                </tr>

                <tr>
                    <th class="section" colspan="4">Academic Qualification</th>
                </tr>
                <tr>
                    <td class="label">Education</td>
                    <td class="value">{{ $candidate->education->name ?? '' }}</td>
                    <td class="label">Profession</td>
                    <td class="value">{{ $candidate->profession->name ?? '' }}</td>
                </tr>

                <tr>
                    <th class="section" colspan="4">Professional Experience</th>
                </tr>
                <tr>
                    <td class="label">Experience</td>
                    <td class="value">{{ $candidate->experience->name ?? '' }}</td>
                    <td class="label">Applied Post</td>
                    <td class="value">{{ $candidate->jobRole->name ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">Skills</td>
                    <td class="value" colspan="3">{{ $candidate->skills->pluck('name')->implode(', ') }}</td>
                </tr>

                <tr>
                    <th class="section" colspan="4">Language Knowledge</th>
                </tr>
                <tr>
                    <td class="value" colspan="4">{{ $candidate->languages->pluck('name')->implode(', ') }}</td>
                </tr>

                <tr>
                    <th class="section" colspan="4">References</th>
                </tr>
                @forelse ($candidate->professionalReferences as $reference)
                    <tr>
                        <td class="label">Name</td>
                        <td class="value">{{ $reference->name }}</td>
                        <td class="label">Designation</td>
                        <td class="value">{{ $reference->designation }}</td>
                    </tr>
                    <tr>
                        <td class="label">Organization</td>
                        <td class="value">{{ $reference->organization }}</td>
                        <td class="label">Contact</td>
                        <td class="value">{{ $reference->mobile ?: ($reference->email ?? '') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="value" colspan="4">N/A</td>
                    </tr>
                @endforelse

                <tr class="signature-row">
                    <td colspan="2">Date: {{ now()->format('Y-m-d') }}</td>
                    <td colspan="2" style="text-align: right;">Signature of Applicant</td>
                </tr>
                <tr>
                    <td class="label">Remarks</td>
                    <td colspan="3"></td>
                </tr>
            </table>
        </div>
        @if (! $loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>

</html>