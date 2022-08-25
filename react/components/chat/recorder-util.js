URL = window.URL || window.webkitURL;

let gumStream; 						//stream from getUserMedia()
let rec; 							//Recorder.js object
let input; 							//MediaStreamAudioSourceNode we'll be recording

// shim for AudioContext when it's not avb. 
let AudioContext = window.AudioContext || window.webkitAudioContext;
let audioContext //audio context to help us record

export const startRecording = (e) => {
    let constraints = { audio: true, video:false }

	/*
    	We're using the standard promise based getUserMedia() 
    	https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia
	*/

	navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
		console.log("getUserMedia() success, stream created, initializing Recorder.js ...");

		/*
			create an audio context after getUserMedia is called
			sampleRate might change after getUserMedia is called, like it does on macOS when recording through AirPods
			the sampleRate defaults to the one set in your OS for your playback device
		*/
		audioContext = new AudioContext();

		//update the format 

		/*  assign to gumStream for later use  */
		gumStream = stream;
		
		/* use the stream */
		input = audioContext.createMediaStreamSource(stream);

		/* 
			Create the Recorder object and configure to record mono sound (1 channel)
			Recording 2 channels  will double the file size
		*/
		rec = new Recorder(input,{numChannels:1})

		//start the recording process
		rec.record()

		console.log("Recording started");

	}).catch(function(err) {
        console.log(err)
	});
}

export const downloadAudio = () => {
    stopRecord()
    rec.exportWAV((blob) => {
        let url = window.URL.createObjectURL(blob);
        let a = document.createElement("a");
        document.body.appendChild(a);
        a.style = "display: none";
        a.href = url;
        a.download = "sample.wav";
        a.click();
        window.URL.revokeObjectURL(url);
    });

    
}
export const getURL = () => {
    stopRecord()
	return new Promise(resolve => {
		rec.exportWAV((blob) => {
			let url = window.URL.createObjectURL(blob);
			resolve(url);
		});
	});
    
}

export const getFile = () => {
	stopRecord();
	return new Promise(resolve => {
		rec.exportWAV((blob) => {
			resolve(blob)
		});
	});
}

export const clearRecord = () => {
	rec.clear();
}

const stopRecord = () => {

    console.log("stopButton clicked");
    
	//tell the recorder to stop the recording
	rec.stop();

	//stop microphone access
	gumStream.getAudioTracks()[0].stop();
}
