export default class Queue {
  constructor(respectPromise = false) {
    this.running = false;
    this.queue = [];
    this.respectPromise = respectPromise;
  }

  add(callback) {
    this.queue.push(() => {
      const finished = callback();

      if (this.respectPromise && finished instanceof Promise) {
        finished
          .then(() => {
            this.next();
          })
          .catch(() => {
            this.next();
          });
      } else if(typeof finished === 'undefined' || finished) {
        this.next();
      }
    });

    if(!this.running) {
      this.next();
    }

    return this;
  }

  next() {
    this.running = false;

    const shift = this.queue.shift();

    if(shift) {
      this.running = true;
      shift();
    }
  }
}
