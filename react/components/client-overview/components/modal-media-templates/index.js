/*jshint esversion: 6 */
import React, { useEffect, useState, Fragment } from 'react';
import "react-datetime/css/react-datetime.css";
import './styles.scss'
import ModalComponent from "../ClientDetails/Modules/ModalComponent";
import SectionInfoComponent from "../ClientDetails/Modules/SectionInfoComponent";
import * as clients from "../../store/clients/actions";
import { connect } from 'react-redux';

const ModalMediaTemplates = React.memo((props) => {
    const {
        show,
        onClose,
        type,
        applyMedia,
        currentMedia,
        libraryMedia,
        mediaLibraryLoading,
        postNewClientDocLibraryAction
    } = props;

    const initialForm = {
        show: false,
        title: '',
        comment: '',
        files: ''
    }

    const initialMedia = {
        show: true,
        title: '',
        comment: '',
        files: ''
    }

    const [templates, setTemplates] = useState([]);
    const [appliedMedia, setAppliedMedia] = useState([]);
    const [newMediaForm, setNewMediaForm] = useState(initialForm);

    useEffect(() => {
        if (show) {
            setAppliedMedia(currentMedia.map(media => media.id));
        }

        return () => {
            setNewMediaForm(initialForm)
        }
    }, [show]);

    useEffect(() => {setTemplates(libraryMedia); return () => {setTemplates([])}},[libraryMedia]);

    const selectMedia = (id, e) => {
        e.preventDefault();
        applyMedia(id);
        setAppliedMedia(appliedMedia => [...appliedMedia,id])
    };

    const updateNewMediaForm = ( value, name ) => {
        setNewMediaForm({...newMediaForm, [name]: value});
    }
    const postNewMedia = () => {
        let formData = new FormData();
        formData.append(0, newMediaForm.files);
        formData.append('title', newMediaForm.title);
        formData.append('comment', newMediaForm.comment);
        type === 'video'
            ? console.log(newMediaForm,"missing action")
            : postNewClientDocLibraryAction(formData)
        document.querySelector(".newMediaForm input").value = '';
        document.querySelector(".newMediaForm textarea").value = '';
        document.querySelector(".newMediaForm #zenfitFile").value = '';
        setNewMediaForm(initialMedia)
    }

    const Content = ({ item }) => {
        const title = type === 'video' ? item.title : item.name;
        const url = type === 'video' ? item.url : item.file;
        const isApplied = appliedMedia && appliedMedia.some(id => id === item.id)
        const btn = isApplied ? `${type} applied` : `Apply ${type}`;
        return (
            <div className='mediaItem'>
                <div className="bottom">
                    <span className={"mediaTitle"}>
                        {title}
                    </span>
                    <button
                        className='zenfitBtn'
                        style={{
                            backgroundColor: isApplied && "#3dd598",
                            pointerEvents: isApplied && "none",
                            textTransform: "capitalize",
                            minWidth: "15rem"
                        }}
                        onClick={(e) => selectMedia(item.id, e)}>
                        {btn}
                    </button>
                </div>
            </div>
        )
    }

    const UploadNewMediaButton = () => {
      return(
          type !== "video" && (
              <div>
                  {
                      newMediaForm.show && (
                          <button className={"zenfitBtn"} disabled={!newMediaForm.files} onClick={postNewMedia}>
                              {mediaLibraryLoading ? "Loading...." : `Save ${type}`}
                          </button>
                      )
                  }
                  <button
                      className={"zenfitWhiteBtn"}
                      style={{marginLeft:"1rem"}}
                      onClick={() => newMediaForm.show
                              ? setNewMediaForm({...newMediaForm,file: '',show: false})
                              : updateNewMediaForm(true,'show')
                      }
                  >
                      {newMediaForm.show ? "X" :`Upload new ${type}s`}
                  </button>
              </div>

          )
      )
    }

    const UploadNewMediaForm = () => {
        return(
            <div className={"newMediaForm"}>
                <SectionInfoComponent
                    title={"Title"}
                    value={newMediaForm.title}
                    type={'input'}
                    name={'title'}
                    valueChange={updateNewMediaForm}
                />
                <SectionInfoComponent
                    title={"Comment"}
                    value={newMediaForm.comment}
                    type={'textarea'}
                    name={'comment'}
                    valueChange={updateNewMediaForm}
                />
                <SectionInfoComponent
                    title={"Upload document"}
                    value={newMediaForm.files}
                    type={'file'}
                    fileType={'application/pdf'}
                    name={'files'}
                    valueChange={updateNewMediaForm}
                />
            </div>
        )
    }

    return (
        <ModalComponent
            open={show} onClose={onClose}
            title={`Apply a ${type} to the client`}
            titleButton={UploadNewMediaButton()}
            mediaUploadForm={newMediaForm.show && UploadNewMediaForm()}
            className={'mediaModal'}
        >
            <Fragment>
                {
                    templates.length === 0 ? (
                        <Fragment>
                            <p>
                                {
                                    type === "video"
                                    ? `No videos have been uploaded`
                                    : `No documents have been uploaded`
                                }
                            </p>
                            <a target={"_blank"} href={type === "video" ? "/video" : "/dashboard/documentOverview"}>
                                <button className={"zenfitBtn"}> Upload new {type}s</button>
                            </a>
                        </Fragment>

                        ):(
                            templates.map((item,index) =>  <Fragment key={index}> <Content item={item} /></Fragment>)
                        )
                }
            </Fragment>
        </ModalComponent>
    );
})

function mapStateToProps(state){
    return{
        libraryMedia:state.clients.libraryMedia,
        mediaLibraryLoading:state.clients.mediaLibraryLoading
    }
}

export default connect(mapStateToProps,{...clients})(ModalMediaTemplates)
