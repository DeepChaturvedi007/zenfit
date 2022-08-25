import React from 'react';

const VersionAlert = ({ url, content }) => {
    return (
        <div className="client-version-alert" onClick={() => window.location.href='/dashboard/clientOverview?bypass=1'}>
            <div className="alert alert-info custome" role="alert">
                Click here to go to back to the old version of the client overview.
            </div>
        </div>
    )
}

export default VersionAlert;
