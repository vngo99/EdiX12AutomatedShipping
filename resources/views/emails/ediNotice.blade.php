<!DOCTYPE html>
<html>
<head>
    <title>Dealeraccess.org</title>
</head>
<body>
    <h3>{{ $details['title'] }}</h3>
    <p>{{ $details['body'] }}</p>

    @if ($details['address'])

    <p>Address:<br/>
        {{$details['address']['street']}}<br/>
        @if ($details['address']['street2'])
            {{$details['address']['street2']}}<br/>
        @endif
        {{$details['address']['city']}},{{$details['address']['state']}} {{$details['address']['zip']}}<br/>
        {{$details['address']['country']}}</p>
                            
    @endif

    @if($details['change'])

    <p>Should be:<br/>
        {{$details['change']['Street']}}<br/>
        @if ($details['change']['Street2'])
            {{$details['change']['Street2']}}<br/>
        @endif
        {{$details['change']['City']}},{{$details['change']['State']}}, {{$details['change']['ZipCode']}}<br/>
        {{$details['change']['Country']}}</p>
                        
    @endif

   
    <p>Thank you</p>
</body>
</html>