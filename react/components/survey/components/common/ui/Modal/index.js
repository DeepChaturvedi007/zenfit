import React, {forwardRef} from 'react';
import {
  CloseButton as BaseCloseButton,
  ModalHeader as BaseHeader,
  Modal as BaseModal,
  ModalBody as BaseModalBody,
  ModalFooter as BaseModalFooter,
} from "react-bootstrap";

export const Modal = forwardRef(({className = '', ...props}, ref) =>
  <BaseModal
    ref={ref}
    className={`${className}`}
    {...props}
  />
);

export const ModalHeader = forwardRef(({className = '', ...props}, ref) =>
  <BaseHeader
    ref={ref}
    className={`${className}`}
    {...props}
  />
);

export const ModalBody = forwardRef(({ className = '', ...props}, ref) =>
  <BaseModalBody
    ref={ref}
    className={`${className}`}
    {...props}
  />
);

export const ModalFooter = forwardRef(({className = '', ...props}, ref) =>
  <BaseModalFooter
    ref={ref}
    className={`${className}`}
    {...props}
  />
);

export const CloseButton = forwardRef(({className = '', ...props}, ref) =>
  <BaseCloseButton
    ref={ref}
    className={`${className}`}
    {...props}
  />
);

export default Modal;
