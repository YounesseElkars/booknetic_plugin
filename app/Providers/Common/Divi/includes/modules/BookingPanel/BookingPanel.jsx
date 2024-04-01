// External Dependencies
import React, {Component, useEffect, useRef} from 'react';

// Internal Dependencies
import './style.css';
const $ = window.jQuery;

class BookingPanel extends Component {

  static slug = 'booknetic_booking_panel';

  render(props) {

    return (
        <Bpanel {...this.props} />
    );
  }

}



function buildShortcode(props)
{
  let shortcode = '[booknetic';
  if(props.staff)
  {
    shortcode+= " staff="+props.staff;
  }
  if(props.theme)
  {
    shortcode+= " theme="+props.theme;
  }
  if(props.location)
  {
    shortcode+= " location="+props.location;
  }
  if(props.service)
  {
    shortcode+= " service="+props.service;
  }
  if(props.category)
  {
    shortcode+= " category="+props.category;
  }
  return shortcode+']';
}

function Bpanel(props){

  const ref = useRef();

  const fetchView = async (shortcode)=>{
    let data = new FormData();
    data.append('shortcode',shortcode)

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
  },[props.staff,props.location,props.service,props.theme,props.category])

  return (
      <div style={{pointerEvents:"none"}} ref={ref}>
        Loading...
      </div>
  );
}


export default BookingPanel;
