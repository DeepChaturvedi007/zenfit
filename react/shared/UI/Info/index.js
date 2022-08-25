import React from "react";
import './styles.scss';

const ZFInfo = (props) => {
    const { title, content } = props;

    return (
        <div className="zf-info row">
            <div className="zf-info--left col-sm-4">
                <p className="zf-info--title">{title}</p>
                <p className="zf-info--content" ><span dangerouslySetInnerHTML={{ __html: content }} /></p>
            </div>
            <div className="zf-info--right col-sm-8">
                {props.children}
            </div>
        </div>
    );
};

export default ZFInfo;