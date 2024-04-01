// External Dependencies
import React, {Component, useEffect, useRef} from 'react';

// Internal Dependencies
import './style.css';
const $ = window.jQuery;

class BookingPopup extends Component {

  static slug = 'booknetic_booking_popup';

  render(props) {

    return (
        <Bpanel {...this.props} />
    );
  }

}



function buildShortcode(props)
{
  let shortcode = '[booknetic-booking-button';

  let list = ['staff','category','service','location','theme','class','caption','style'];

  list.forEach((v,i)=>{
    if( props[v] )
      shortcode+= ` ${v}="${props[v]}"`
  });

  return shortcode+="]";
}

function Bpanel(props){

  const ref = useRef();

  const fetchView = async (shortcode)=>{

    let data = new FormData();

    data.append('shortcode',shortcode )

    let bookneticDiviOptions = JSON.parse( decodeURIComponent( props.bookneticDivi ) );

    let req = await fetch(bookneticDiviOptions.url + '/?bkntc_preview=1',{
      method:'POST',
      body:data
    });

    let res = await req.text();
    $(ref.current).html(res)
  }

  useEffect(()=>{
    fetchView(buildShortcode(props))
  },[props.staff,props.location,props.service,props.theme,props.category,props.caption,props.class,props.style])

  return (
      <div style={{pointerEvents:"none"}} ref={ref}>
        Loading...
      </div>
  );
}


export default BookingPopup;
