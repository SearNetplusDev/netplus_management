<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: 'Garamond';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Garamond/static/EBGaramond-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'Bodoni';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Bodoni/static/LibreBodoni-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'Didot';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Didot/GFSDidot-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'Lora';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Lora/static/Lora-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Montserrat/static/Montserrat-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'Raleway';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Raleway/static/Raleway-Regular.ttf') }}") format("truetype");
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            font-size: 12px;
            line-height: 1.5em;
            color: #333;
            background: #fff;
            margin: 1.5cm 1.9cm;
            border: 1px solid cornflowerblue;
        }

        .header {

        }

        .content {
            /*font-family: 'Garamond', sans-serif;*/
            /*font-family: 'Didot', sans-serif;*/
            /*font-family: 'Lora', sans-serif;*/
            /*font-family: 'Montserrat', sans-serif;*/
            /*font-family: 'Raleway', sans-serif;*/
            font-family: 'Bodoni', sans-serif;
            font-size: 1.5em;
        }

    </style>
</head>
<body>
<div class="header">
    {{$data}}
</div>
<div class="content">
    {{$data['client']['name'].' '.$data['client']['surname']}}
</div>
<div style="width: 100%; text-align: justify; font-family: 'Raleway', serif; font-size: 12px;">
    <div>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus ad consequatur dolores eius ipsa,
        recusandae ullam veniam. Dolores excepturi impedit, ipsam nihil officiis possimus quidem quis repellendus veniam
        voluptatibus. Doloremque.
    </div>
    <div>Aperiam consequuntur culpa, deleniti dolor eum fuga nulla quam recusandae sed voluptate? Accusamus alias animi
        asperiores autem, consequuntur doloribus, esse est ipsa ipsam laudantium magni maiores natus quisquam quod
        rerum?
    </div>
    <div>Ad aliquid animi aperiam consequuntur cum deleniti dolores dolorum ducimus eius harum ipsa ipsam ipsum magni
        nesciunt omnis possimus quia quisquam, quos repellendus sit soluta sunt tempora ullam veniam voluptatibus.
    </div>
    <div>A accusamus ad aliquam architecto consectetur dolore doloremque dolorum ducimus eaque eveniet ex expedita fuga
        harum inventore ipsa ipsam laudantium magnam minima, mollitia nobis obcaecati quasi quisquam ratione sequi sint.
    </div>
    <div>Adipisci aliquid consequuntur doloremque nulla odio quo ratione tempore velit vitae voluptatibus! Asperiores
        blanditiis, culpa expedita illo necessitatibus qui voluptate. Cum eligendi fuga, hic inventore nobis optio
        quibusdam sint vitae!
    </div>
    <div>
        {{$data['client']['address']['address']}},
        {{$data['client']['address']['neighborhood']}},
        {{$data['client']['address']['district']['name']}},
        {{$data['client']['address']['municipality']['name']}},
        {{$data['client']['address']['state']['name']}},
        {{$data['client']['country']['name']}}
    </div>
</div>
</body>
</html>
