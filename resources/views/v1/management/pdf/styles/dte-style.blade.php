<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    @page {
        margin: 100px 50px 80px 50px;
    }

    body {
        font-family: 'Calibri', sans-serif;
        font-size: 11px;
        line-height: 1.15em;
        color: #333333;
        margin: 1.0cm 1.5cm;
        text-align: justify;
    }

    .grid {
        width: 100%;
        margin: 0 auto;
    }

    .grid-1 .col {
        display: block;
        width: 100%;
    }

    .grid-2 .col {
        display: inline-block;
        width: 49%;
        vertical-align: top;
    }

    .grid-3 {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    .grid-3 .col {
        display: table-cell;
        width: 33%;
        vertical-align: middle;
    }

    .grid-3 .col-1 {
        display: table-cell;
        width: 20%;
    }

    .grid-3 .col-2 {
        display: table-cell;
        width: 60%;
    }

    .title-h1 {
        font-size: 1.5em;
        font-weight: bold;
        text-align: center;
    }

    .mt-xs {
        margin-top: 5px;
    }

    .align-images {
        vertical-align: middle;
        text-align: center;
    }

    .align-header-text {
        vertical-align: middle;
        padding: 0 1.5em;
    }

    .font-bold {
        font-weight: bold;
    }

</style>
