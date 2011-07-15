<?php
/*
* class to contain useful functions for generating dates within an academic year
* should be initialised with a list of start and end dates of terms, and it should calculate all other necessary dates
* eg weeks, months etc
*/
class calendarfuncs{

    public $termdatelist;  //for each term, array( start, end )
    protected $timeformat;    //mysql or unix
    protected $firstdayofweek;//Sunday, Monday or another day
    public $readabledateformat = 'Y-m-d H:i:s';
    //protected $weekdays = array( 'Sunday', ' Monday', ' Tuesday', ' Wednesday', ' Thursday', ' Friday', ' Saturday');

    /*
    * @param array of arrays $termdatelist
    */
    public function __construct( $termdatelist=array() ){
        if( $termdatelist ){
            $this->termdatelist = $termdatelist;
        }
        else{
            $this->termdatelist = array(
                array( '2010-10-01', '2010-12-17' ),
                array( '2011-01-04', '2011-03-25' ),
                array( '2011-04-13', '2011-06-30' )
            );
        }
        $this->timeformat = 'mysql';
        $this->firstdayofweek = 1;    //1=Monday, 7=Sunday ... please do not use 0
    }
    
    /*
    *
    */
    public function display_calendar(){
        var_crap( $this->generate_dates() );
    }
    
    /*
    * @return array of arrays
    */
    public function generate_dates(){
        $dateinfo = array();
        $counter = 0;
        foreach( $this->termdatelist as $startend ){
            $counter++;
            $termstart = $startend[ 0 ];
            $termend = $startend[ 1 ];
            $termname = "TERM $counter";
            $dateinfo[] = $this->generate_sub_dates( $termstart, $termend, $termname );
        }
        return $dateinfo;
    }

    /*
    * @param mixed $start
    * @param mixed $end
    * @param string $name
    */
    public function generate_sub_dates( $start, $end, $name ){
        $startdayofweek = $this->calc_day_of_week( $start );
        $enddayofweek = $this->calc_day_of_week( $end ); return array(
            'name' => $name,
            'start' => "$startdayofweek $start",
            'end' => "$enddayofweek $end",
            'months' => $this->calc_sub_month_limits( $start, $end ),
            'weeks' => $this->calc_sub_week_limits( $start, $end )
        );
    }

    /*
    * take a start date and end date and return a list of datetime objects, one for each day from start to end
    * @param mixed $start
    * @param mixed $end
    * @return array of datetime
    */
    public function calc_daylist( $start, $end ){
        $dt_start = new DateTime( $start );
        $dt_end = new DateTime( $end );
        $daylist = array();
        $tmp = clone( $dt_start );
        while( $tmp->getTimestamp() <= $dt_end->getTimestamp() ){
            $daylist[] = $tmp;
            $tmp = clone( $tmp->modify( '+1 day' ) );
        }
        return $daylist;
    }

    /*
    * @param mixed $start
    * @param mixed $end
    * @return array of arrays
    */
    public function calc_sub_week_limits( $start, $end, $date_format=null ){
        if( !$date_format ){
            $date_format = $this->readabledateformat;
        }
        $dt_start = new DateTime( $start );
        $dt_end = new DateTime( $end );
        $weekslist = array();

        $tmp = clone( $dt_start );
        $weekstart = clone( $tmp );
        while( $tmp->format( 'N' ) != $this->firstdayofweek ){
	            $tmp = $tmp->modify( '+1 day' );
        }
        $next_weekstart = clone( $tmp );
        $weekend = $tmp->modify( '-1 day' );
        //$weekslist[] = array( $weekstart->format( $date_format ), $weekend->format( $date_format ) );
        $tmp = clone( $next_weekstart );
        
            //now add complete weeks until we hit $end
        $counter = 0;
        while( $tmp->getTimestamp() < $dt_end->getTimestamp() ){
            $tmp = clone( $next_weekstart );
            $tmp->modify( '+ 6 day' );
            if( $tmp->getTimestamp() > $dt_end->getTimestamp() ){
                $next_weekend = clone( $dt_end );
            }
            else{
                $next_weekend = clone( $tmp );
            }
            $weekslist[] = array( $next_weekstart->format( $date_format ) , $next_weekend->format( $date_format ) );
            $next_weekstart = $tmp->modify( '+1 day' );
        }
        return $weekslist;
    }
    
    /*
    * @param mixed $start
    * @param mixed $end
    * @return array of arrays
    */
    public function calc_sub_month_limits( $start, $end ){
        $utime_start = $this->getutime( $start );
        $utime_end = $this->getutime( $end );

        if( $utime_end < $utime_start ){
            list( $utime_end, $utime_start ) = array( $utime_start, $utime_end );
        }

        $startmonth = date( 'n' , $utime_start );
        $endmonth = date( 'n' , $utime_end );
        $startyear = date( 'Y' , $utime_start );
        $endyear = date( 'Y' , $utime_end );

        $tmp = $utime_start;
        $tmpmonth = date( 'n', $tmp );
        $tmpyear = date( 'Y', $tmp );
            if( $tmpmonth == $endmonth && $tmpyear == $endyear ){
                $lastday = date( 'd', $utime_end );
            }
            else{
                $lastday = date( 't', $tmp );
            }
            $lastdateofmonth = mktime( 0, 0, 0, $tmpmonth, $lastday, $tmpyear );
        $monthdatelist = array( array( $this->getreadabletime( $tmp ), $this->getreadabletime( $lastdateofmonth ) ) );

        $tmpmonth = date( 'n', $tmp );
        $tmpyear = date( 'Y', $tmp );
        while( !( $tmpmonth == $endmonth && $tmpyear == $endyear ) ){
            $tmpmonth = date( 'n', $tmp );
            $tmpyear = date( 'Y', $tmp );
            //increment the month
            $newmonth = ( $tmpmonth + 1 );
            $newyear = $tmpyear;
            if( $newmonth > 12 ){
                $newmonth -= 12;
                $newyear++;
            }
            $tmp = mktime( 0, 0, 0, $newmonth, 1, $newyear );
            $tmpmonth = date( 'n', $tmp );
            $tmpyear = date( 'Y', $tmp );
            //test the condition again
            if( $tmpmonth == $endmonth && $tmpyear == $endyear ){
                $lastday = date( 'd', $utime_end );
            }
            else{
                $lastday = date( 't', $tmp );
            }
            $lastdateofmonth = mktime( 0, 0, 0, $tmpmonth, $lastday, $tmpyear );
            $monthdatelist[] = array( $this->getreadabletime( $tmp ), $this->getreadabletime( $lastdateofmonth ) );
        }
        //final month
            
        return $monthdatelist;
    }

    public function get_clocktime( $dtime ){
        $utime = $this->getutime( $dtime );
        return date( 'H:i', $utime );
    }

    public function get_dayname( $dtime ){
        return $dtime;
    }

    /*
    * take date in varying formats, and return consistent unix time
    * @param mixed $date
    * @return int
    */
    public function getutime( $date ){
        if( is_numeric( $date ) ){
            //assume unix time already
            $utime = $date;
        }
        else{
            //mysql format
            $dateparts = explode( ' ', $date );
            list( $year, $month, $date ) = $this->split_mysql_date( $dateparts[ 0 ] );
            if( count( $dateparts ) > 1 ){
                //time as well
                list( $hours, $minutes, $seconds ) = $this->split_mysql_date( $dateparts[ 1 ] , ':' );
            }
            else{
                //assume 'year-month-date'
                list( $hours, $minutes, $seconds ) = array( 0,0,0 );
            }
            $utime = mktime( $hours, $minutes, $seconds, $month, $date, $year );
        }
        return $utime;
    }

    public function get_time( $timestamp ){
        return date( 'H:i', $this->getutime( $timestamp ) );
    }
    
    public function getreadabletime( $utime, $format='Y-m-d H:i:s' ){
        return date( $format, $utime );
    }

    public function calc_day_of_week( $date, $date_format='N' ){
        $weekdaynum = date( $date_format, $this->getutime( $date ) );
        return $weekdaynum;
    }

    public function calc_weekno( $date1, $date2, $format='%d' ){
        $dt_date1 = $this->getutime( $date1 );
        $dt_date2 = $this->getutime( $date2 );
        $interval = $dt_date2 - $dt_date1;
        $days = $interval / ( 3600 * 24 );
        return intval( ceil( $days / 7 ) );
    }
    
    public function split_mysql_date( $date, $sep="-" ){
        $dateparts = explode( ' ' , $date );
        $date = $dateparts[ 0 ];
        return explode( $sep, $date );
    }
}
