// External Dependencies
import React, {Component, useEffect, useRef} from 'react';

// Internal Dependencies
import './style.css';
const $ = window.jQuery;

class SignUp extends Component {

  static slug = 'booknetic_signup';

  render(props) {

    return (
        <Bpanel {...this.props} />
    );
  }

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
    fetchView('[booknetic-signup]')
  },[])

  return (
      <div style={{pointerEvents:"none"}} ref={ref}>
        Loading...
      </div>
  );
}


export default SignUp;
