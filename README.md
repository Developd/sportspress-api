# SportsPress API

A small, incomplete, API for the SportsPress WordPress plugin.

## Example Usage

Get Games by Venue:
```php
get_events_by_venue( $venue_id );
```

Get Games that are scheduled for today:
```php
->today()
```

Include the teams for each game:
```php
->with( 'teams' )
```

Include the teams and results for each game:
```php
->with( [ 'teams', 'results' ] )
```

Get Games by Venue, for today only, include teams and results:
```php
$events = SP_API()->with( [ 'teams', 'results' ] )->today()->get_events_by_venue( $venue_id );
```
