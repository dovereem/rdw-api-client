# rdw-api-client

## Introduction

This is a simple API client to pull vehicle data from the RDW (Rijksdienst Wegverkeer) OpenData API.

See: https://opendata.rdw.nl for more information.

Because this is Open Data provided by the government an API key is not needed.

## Usage:

```
$rdwApiClient = new \DOvereem\RdwApiClient\RdwApiClient();
$vehicleData = $rdwApiClient->getLicensedVehicleDataByLicensePlateNumber("AA-BB-CC");
```

