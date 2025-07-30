{{ $greetings }}<br><br>

{{ $header }}<br><br>

<a href="{{ URL::to('/') }}/job_requests" target="_blank" style="font-family: Arial, Calibri; font-weight: bold;">JOB Request System</a>
<br><br>

<table>
    <tr>
        <td>Date:</td>
        <td><span>{{ date('Y-m-d') }}</span></td>
    </tr>
    <tr>
        <td>Project Name: </td>
        <td><span style="font-family: Arial, Calibri; font-weight: bold;">{{ $project_name }}</span></td>
    </tr>
</table>
<br>

<table>
    <tr>
        <td>From: </td>
        <td>
            <span style="font-family: Arial; font-weight: bold;">
                {{ $sender_username }}
            </span>
        </td>
    </tr>
</table>

<br>

<table width="550">
    <tr>
        <td style="font-family: Arial, Calibri; font-weight: bold;">Document</td>
        <td style="font-family: Arial, Calibri; font-weight: bold;">No. of File/s</td>
        <!-- <td style="font-family: Arial, Calibri; font-weight: bold;">ECD</td> -->
        <td style="font-family: Arial, Calibri; font-weight: bold;">Date Uploaded</td>
    </tr>
    @foreach ($documents_uploaded as $item)
    <tr>
        @if (isset($new_uploads) && in_array($item->document_id, $new_uploads))
            <td style="color: blue;">
                {{ $item->required_name }}
            </td>
        @else
            <td>
                {{ $item->required_name }}
            </td>
        @endif
        <td>
            @if ($item->date_uploaded !== null)
                {{ $item->uploads->count() }}
            @endif
        </td>
            @if (isset($new_uploads) && in_array($item->document_id, $new_uploads))
                <td style="color: blue;">
                    @if ($item->date_uploaded !== null)
                        {{date('Y-m-d', strtotime($item->date_uploaded))}}
                    @else
                        N/A
                    @endif
                </td>
            @else
                <td>
                    @if ($item->date_uploaded !== null)
                        {{date('Y-m-d', strtotime($item->date_uploaded))}}
                    @else
                        N/A
                    @endif
                </td>
            @endif
    </tr>
    @endforeach
</table>
<br><br>

*Please do not reply to this email. This is an auto-generated email.<br><br>

Iconn System