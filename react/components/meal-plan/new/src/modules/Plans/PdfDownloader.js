import React, { memo, useState, useEffect } from "react";
import { useLoads } from "react-loads";
import { timeout } from "../../utils/helpers";
import { CardActivity, CardActivityIcon, CardActivityText } from "../../components/Card";
import * as api from "../../utils/api";

const MESSAGES = {
  pending: 'Loading...',
  resolved: 'The PDF will be sent to your email.',
  rejected: 'Cannot generate PDF, try again.',
};

const PdfDownloader = memo(({ plan, maxAttempts, onFlush }) => {
  const [attempts, setAttempts] = useState(0);
  const fetch = () => api.fetchPdf(plan);
  const { response, load, isRejected, isPending, isResolved } = useLoads(
    fetch,
    {},
    [plan.id, attempts]
  );

  const canRetry = attempts < maxAttempts;

  const retry = () => {
    if (canRetry) {
      load();
      setAttempts(attempts + 1);
    }
  };

  useEffect(() => {
    if (response) {
      //response finished successfully
    } else if (isResolved) {
      timeout(2000).then(onFlush);
    }
  }, [isResolved]);

  useEffect(() => {
    if (attempts >= maxAttempts) {
      timeout(2000).then(onFlush);
    }
  }, [isRejected, attempts]);

  let status;

  if (isPending) {
    status = 'pending';
  } else if (isResolved) {
    status = 'resolved';
  } else if (isRejected) {
    status = 'rejected';
  }

  return (
    <CardActivity type={status} loading={isPending}>
      <CardActivityText>{MESSAGES[status]}</CardActivityText>
      {(isRejected && canRetry) && (
        <button type="button" onClick={retry}>
          Retry
        </button>
      )}
    </CardActivity>
  );
});

PdfDownloader.defaultProps = {
  maxAttempts: 3,
};

export default PdfDownloader;
