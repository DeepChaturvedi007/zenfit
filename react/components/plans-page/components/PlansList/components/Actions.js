import React, {useState} from "react";
import swal from 'sweetalert';

const Actions = ({item, onDelete}) => {
  const [deleting, setDeleting] = useState(false);
  const handleRemoveAction = () => {
    if(deleting) return;
    const callback = () => {
      setDeleting(true);
      onDelete(item)
        .then(() => setDeleting(false))
        .catch(() => setDeleting(false));
    };
    if(swal) {
      swal({
        title: 'Warning!',
        text: 'By confirming this action, you agree that the selected plan will be deleted. Continue?',
        dangerMode: true,
        buttons: {
          cancel: {
            text: 'Cancel',
            value: null,
            visible: true,
            closeModal: true
          },
          confirm: {
            text: 'Confirm',
            value: true,
            visible: true,
            closeModal: true
          }
        }
      })
        .then(confirmed => {
          confirmed && callback();
        })
    } else {
      confirm('Delete this item?') && callback();
    }
  };

  return (
    <span className={'actions'}>
      <i className={'fa fa-trash'} onClick={handleRemoveAction} />
    </span>
  );
};

export default Actions;