/*
 ---
 description: MooSocket class, a basic WebSocket wrapper for MooTools

 license: MIT-style

 authors:
 - Trae Robrock

 requires:
 - core/1.3: '*'

 provides: [MooSocket]

 ...
 */

var MooSocket = new Class({
    Implements: [Options, Events],

    options: {
        reconnect: false,
        maxReconnects: 10,
        onOpen: Function.from(),
        onMessage: Function.from(),
        onClose: Function.from(),
        onError: Function.from()
    },
    reconnectDelay: 0,
    reconnectAttempts: 0,

    initialize: function (location, options) {
        if (!("WebSocket" in window)) throw 'This browser cannot use sockets';

        this.setOptions(options);
        this.location = location;
        this.create()
    },

    create: function () {
        try {
            this.socket = new WebSocket(this.location);
        }
        catch (e){
            this.fireEvent("error", e);
        }

        this.attachEvents();
    },

    attachEvents: function () {
        this.socket.onmessage = function (e) {
            this.fireEvent("message", [e.data, e]);
        }.bind(this);

        this.socket.onclose = function (e) {
            this.fireEvent("close");
            if (this.options.reconnect) this.reconnect();
        }.bind(this);

        this.socket.onopen = function (e) {
            this.fireEvent("open", e);
        }.bind(this);

        this.socket.onerror = function (e) {
            this.fireEvent("error", e);
        }.bind(this)

        return this
    },
    send: function(msg){
        this.socket.send(msg);
    },
    close: function(){
        this.socket.close();
    },
    reconnect: function () {
        if (this.reconnectAttempts > this.options.maxReconnects) return false;
        this.create.delay(this.reconnectDelay * 1000);
        this.reconnectAttempts++;
        this.reconnectDelay = 2 * this.reconnectAttempts;
    }
});