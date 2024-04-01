// External Dependencies
import React, {Component, useEffect, useRef} from 'react';

// Internal Dependencies
import './style.css';
const $ = window.jQuery;

class ChangeStatus extends Component {

  static slug = 'booknetic_change_status';

  render(props) {

    return (
        <Bpanel {...this.props} />
    );
  }

}



function buildShortcode(props)
{
  let shortcode = '[booknetic-change-status';

  let list = ['label','successLabel','button','successButton'];

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
  },[props.label,props.successLabel,props.button,props.successButton])

  return (
      <div style={{pointerEvents:"none"}} ref={ref}>
        Loading...
      </div>
  );
}


export default ChangeStatus;
