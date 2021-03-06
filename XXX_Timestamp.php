<?php


// TODO Check Shorthand year, since 1900, so 2023 = 23 or 123?

// UTC (Global) based - No timezones, DST etc.
class XXX_Timestamp
{
	protected $timestamp = 0;
	
	public function __construct ($tempParameter = false)
	{
		$this->timestamp = time();
		
		if (XXX_Type::isTimestamp($tempParameter))
		{
			$this->set($tempParameter->get());
		}		
		else if (XXX_Type::isArray($tempParameter))
		{
			$this->compose($tempParameter);
		}
		else if (XXX_Type::isString($tempParameter) && XXX_String_Pattern::hasMatch($tempParameter, '^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$'))
		{
			$parts = array();
			
			$parts['year'] = XXX_Type::makeInteger(XXX_String::getPart($tempParameter, 0, 4));
			$parts['month'] = XXX_Type::makeInteger(XXX_String::getPart($tempParameter, 5, 2));
			$parts['date'] = XXX_Type::makeInteger(XXX_String::getPart($tempParameter, 8, 2));
			$parts['hour'] = XXX_Type::makeInteger(XXX_String::getPart($tempParameter, 11, 2));
			$parts['minute'] = XXX_Type::makeInteger(XXX_String::getPart($tempParameter, 14, 2));
			$parts['second'] = XXX_Type::makeInteger(XXX_String::getPart($tempParameter, 17, 2));
			
			$this->compose($parts);
		}
		else if (XXX_Type::isString($tempParameter) && XXX_String_Pattern::hasMatch($tempParameter, '^[0-9]{4}-[0-9]{2}-[0-9]{2}\s[0-9]{2}:[0-9]{2}:[0-9]{2}$'))
		{
			$parts = array();
			
			$parts['year'] = XXX_Type::makeInteger(XXX_String::getPart($tempParameter, 0, 4));
			$parts['month'] = XXX_Type::makeInteger(XXX_String::getPart($tempParameter, 5, 2));
			$parts['date'] = XXX_Type::makeInteger(XXX_String::getPart($tempParameter, 8, 2));
			$parts['hour'] = XXX_Type::makeInteger(XXX_String::getPart($tempParameter, 11, 2));
			$parts['minute'] = XXX_Type::makeInteger(XXX_String::getPart($tempParameter, 14, 2));
			$parts['second'] = XXX_Type::makeInteger(XXX_String::getPart($tempParameter, 17, 2));
			
			$this->compose($parts);
		}
		else if (XXX_Type::isInteger($tempParameter))
		{
			$this->set($tempParameter);
		}
	}
		
	// Seconds
	public function set ($timestamp)
	{
		if (XXX_Type::isInteger($timestamp))
		{
			$this->timestamp = $timestamp;
		}
	}
	
	// Seconds
	public function get ()
	{
		return $this->timestamp;
	}
	
	public function makeLocal ()
	{
		$this->timestamp += XXX_TimestampHelpers::getLocalSecondOffset();
	}	
	
	// Reversed of makeLocal
	public function makeGlobal ()
	{
		$this->timestamp -= XXX_TimestampHelpers::getLocalSecondOffset();
	}
	
	public function parse ($extended = false)
	{
		$year = date('Y', $this->timestamp);
		$yearShort = XXX_String::getPart($year, -2, 2);
		
		$dayOfTheWeek = date('w', $this->timestamp);
		
		// Convert Sunday to last day of the week
		if ($dayOfTheWeek == 0)
		{
			$dayOfTheWeek = 7;
		}
		
		$dayOfTheMonth = date('j', $this->timestamp);
		
		$monthOfTheYear = date('n', $this->timestamp);
		
		$hour = date('G', $this->timestamp);
		$minute = date('i', $this->timestamp);
		$second = date('s', $this->timestamp);
		
		$meridiem = 'am';
		
		if ($hour >= 12)
		{
			$meridiem = 'pm';
		}
		
		$hourShort = $hour;
		
		if ($hourShort > 12)
		{
			$hourShort -= 12;
		}
		
		if ($hourShort == 0)
		{
			$hourShort = 12;
		}
		
		// http://en.wikipedia.org/wiki/ISO_8601
		$iso8601 = date('Y-m-d\TH:i:s\Z', $this->timestamp);
		
		$parts = array
		(
			'timestamp' => $this->timestamp,
			'year' => XXX_Type::makeInteger($year),
			'yearShort' => XXX_Type::makeInteger($yearShort),
			'month' => XXX_Type::makeInteger($monthOfTheYear),
			'monthOfTheYear' => XXX_Type::makeInteger($monthOfTheYear),
			'date' => XXX_Type::makeInteger($dayOfTheMonth),
			'dayOfTheMonth' => XXX_Type::makeInteger($dayOfTheMonth),
			'dayOfTheWeek' => XXX_Type::makeInteger($dayOfTheWeek),
			'hour' => XXX_Type::makeInteger($hour),
			'hourShort' => XXX_Type::makeInteger($hourShort),
			'minute' => XXX_Type::makeInteger($minute),
			'second' => XXX_Type::makeInteger($second),
			'meridiem' => $meridiem,
			'iso8601' => $iso8601,
			'string' => $iso8601
		);
		
		if ($extended)
		{
			$parts['dayTotalInMonth'] = XXX_Type::makeInteger(XXX_TimestampHelpers::getDayTotalInMonth($year, $monthOfTheYear));
			$parts['dayTotalInYear'] = XXX_Type::makeInteger(XXX_TimestampHelpers::getDayTotalInYear($year));
			$parts['dayOfTheYear'] = XXX_Type::makeInteger(XXX_TimestampHelpers::getDayOfTheYear($year, $monthOfTheYear, $dayOfTheMonth));
			$parts['leapYear'] = XXX_TimestampHelpers::isLeapYear($year);
			$parts['weekOfTheYear'] = XXX_Type::makeInteger(XXX_Timestamphelpers::iso8601_getWeekOfTheYear($year, $monthOfTheYear, $dayOfTheMonth));
		}
		
		return $parts;
	}
	
	public function compose ($parts = array())
	{
		// Year
		if (!XXX_Type::isInteger($parts['year']))
		{
			$parts['year'] = date('Y');
		}
		
		// Month
		if (!XXX_Type::isInteger($parts['month']) && ($parts['month'] >= 1 && $parts['month'] <= 12))
		{
			$parts['month'] = date('n');
		}
		
		// Date
		if (!XXX_Type::isInteger($parts['date']) && ($parts['date'] >= 1 && $parts['date'] <= 31))
		{
			$parts['date'] = date('j');
		}
		
		if (!XXX_TimestampHelpers::isExistingDate($parts['year'], $parts['month'], $parts['date']))
		{
			$dayTotalInMonth = XXX_TimestampHelpers::getDayTotalInMonth($parts['year'], $parts['month']);
			
			$parts['date'] = $dayTotalInMonth;
		}
		
		// Hour
		if (!XXX_Type::isInteger($parts['hour']) && ($parts['hour'] >= 0 && $parts['hour'] <= 23))
		{
			$parts['hour'] = date('G');
		}
		
		// Minute
		if (!XXX_Type::isInteger($parts['minute']) && ($parts['minute'] >= 0 && $parts['minute'] <= 59))
		{
			$parts['minute'] = XXX_Type::makeInteger(date('i'));
		}
		
		// Second
		if (!XXX_Type::isInteger($parts['second']) && ($parts['second'] >= 0 && $parts['second'] <= 59))
		{
			$parts['second'] = XXX_Type::makeInteger(date('s'));
		}
		
		$this->timestamp = mktime($parts['hour'], $parts['minute'], $parts['second'], $parts['month'], $parts['date'], $parts['year']);
	}
}

?>