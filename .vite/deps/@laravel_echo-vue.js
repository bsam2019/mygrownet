import {
  require_pusher
} from "./chunk-QS5LMDO4.js";
import {
  onMounted,
  onUnmounted,
  ref,
  watch
} from "./chunk-CEBIFPJU.js";
import {
  __toESM
} from "./chunk-2TUXWMP5.js";

// node_modules/@laravel/echo-vue/dist/index.js
var import_pusher_js = __toESM(require_pusher());
var k = class {
  constructor() {
    this.notificationCreatedEvent = ".Illuminate\\Notifications\\Events\\BroadcastNotificationCreated";
  }
  /**
   * Listen for a whisper event on the channel instance.
   */
  listenForWhisper(e, t) {
    return this.listen(".client-" + e, t);
  }
  /**
   * Listen for an event on the channel instance.
   */
  notification(e) {
    return this.listen(this.notificationCreatedEvent, e);
  }
  /**
   * Stop listening for notification events on the channel instance.
   */
  stopListeningForNotification(e) {
    return this.stopListening(this.notificationCreatedEvent, e);
  }
  /**
   * Stop listening for a whisper event on the channel instance.
   */
  stopListeningForWhisper(e, t) {
    return this.stopListening(".client-" + e, t);
  }
};
var A = class {
  /**
   * Create a new class instance.
   */
  constructor(e) {
    this.namespace = e;
  }
  /**
   * Format the given event name.
   */
  format(e) {
    return [".", "\\"].includes(e.charAt(0)) ? e.substring(1) : (this.namespace && (e = this.namespace + "." + e), e.replace(/\./g, "\\"));
  }
  /**
   * Set the event namespace.
   */
  setNamespace(e) {
    this.namespace = e;
  }
};
function B(s) {
  try {
    new s();
  } catch (e) {
    if (e instanceof Error && e.message.includes("is not a constructor"))
      return false;
  }
  return true;
}
var I = class extends k {
  /**
   * Create a new class instance.
   */
  constructor(e, t, n) {
    super(), this.name = t, this.pusher = e, this.options = n, this.eventFormatter = new A(this.options.namespace), this.subscribe();
  }
  /**
   * Subscribe to a Pusher channel.
   */
  subscribe() {
    this.subscription = this.pusher.subscribe(this.name);
  }
  /**
   * Unsubscribe from a Pusher channel.
   */
  unsubscribe() {
    this.pusher.unsubscribe(this.name);
  }
  /**
   * Listen for an event on the channel instance.
   */
  listen(e, t) {
    return this.on(this.eventFormatter.format(e), t), this;
  }
  /**
   * Listen for all events on the channel instance.
   */
  listenToAll(e) {
    return this.subscription.bind_global((t, n) => {
      if (t.startsWith("pusher:"))
        return;
      let i = String(this.options.namespace ?? "").replace(
        /\./g,
        "\\"
      ), r = t.startsWith(i) ? t.substring(i.length + 1) : "." + t;
      e(r, n);
    }), this;
  }
  /**
   * Stop listening for an event on the channel instance.
   */
  stopListening(e, t) {
    return t ? this.subscription.unbind(
      this.eventFormatter.format(e),
      t
    ) : this.subscription.unbind(this.eventFormatter.format(e)), this;
  }
  /**
   * Stop listening for all events on the channel instance.
   */
  stopListeningToAll(e) {
    return e ? this.subscription.unbind_global(e) : this.subscription.unbind_global(), this;
  }
  /**
   * Register a callback to be called anytime a subscription succeeds.
   */
  subscribed(e) {
    return this.on("pusher:subscription_succeeded", () => {
      e();
    }), this;
  }
  /**
   * Register a callback to be called anytime a subscription error occurs.
   */
  error(e) {
    return this.on("pusher:subscription_error", (t) => {
      e(t);
    }), this;
  }
  /**
   * Bind a channel to an event.
   */
  on(e, t) {
    return this.subscription.bind(e, t), this;
  }
};
var x = class extends I {
  /**
   * Send a whisper event to other clients in the channel.
   */
  whisper(e, t) {
    return this.pusher.channels.channels[this.name].trigger(
      `client-${e}`,
      t
    ), this;
  }
};
var q = class extends I {
  /**
   * Send a whisper event to other clients in the channel.
   */
  whisper(e, t) {
    return this.pusher.channels.channels[this.name].trigger(
      `client-${e}`,
      t
    ), this;
  }
};
var U = class extends x {
  /**
   * Register a callback to be called anytime the member list changes.
   */
  here(e) {
    return this.on("pusher:subscription_succeeded", (t) => {
      e(Object.keys(t.members).map((n) => t.members[n]));
    }), this;
  }
  /**
   * Listen for someone joining the channel.
   */
  joining(e) {
    return this.on("pusher:member_added", (t) => {
      e(t.info);
    }), this;
  }
  /**
   * Send a whisper event to other clients in the channel.
   */
  whisper(e, t) {
    return this.pusher.channels.channels[this.name].trigger(
      `client-${e}`,
      t
    ), this;
  }
  /**
   * Listen for someone leaving the channel.
   */
  leaving(e) {
    return this.on("pusher:member_removed", (t) => {
      e(t.info);
    }), this;
  }
};
var V = class extends k {
  /**
   * Create a new class instance.
   */
  constructor(e, t, n) {
    super(), this.events = {}, this.listeners = {}, this.name = t, this.socket = e, this.options = n, this.eventFormatter = new A(this.options.namespace), this.subscribe();
  }
  /**
   * Subscribe to a Socket.io channel.
   */
  subscribe() {
    this.socket.emit("subscribe", {
      channel: this.name,
      auth: this.options.auth || {}
    });
  }
  /**
   * Unsubscribe from channel and ubind event callbacks.
   */
  unsubscribe() {
    this.unbind(), this.socket.emit("unsubscribe", {
      channel: this.name,
      auth: this.options.auth || {}
    });
  }
  /**
   * Listen for an event on the channel instance.
   */
  listen(e, t) {
    return this.on(this.eventFormatter.format(e), t), this;
  }
  /**
   * Stop listening for an event on the channel instance.
   */
  stopListening(e, t) {
    return this.unbindEvent(this.eventFormatter.format(e), t), this;
  }
  /**
   * Register a callback to be called anytime a subscription succeeds.
   */
  subscribed(e) {
    return this.on("connect", (t) => {
      e(t);
    }), this;
  }
  /**
   * Register a callback to be called anytime an error occurs.
   */
  error(e) {
    return this;
  }
  /**
   * Bind the channel's socket to an event and store the callback.
   */
  on(e, t) {
    return this.listeners[e] = this.listeners[e] || [], this.events[e] || (this.events[e] = (n, i) => {
      this.name === n && this.listeners[e] && this.listeners[e].forEach((r) => r(i));
    }, this.socket.on(e, this.events[e])), this.listeners[e].push(t), this;
  }
  /**
   * Unbind the channel's socket from all stored event callbacks.
   */
  unbind() {
    Object.keys(this.events).forEach((e) => {
      this.unbindEvent(e);
    });
  }
  /**
   * Unbind the listeners for the given event.
   */
  unbindEvent(e, t) {
    this.listeners[e] = this.listeners[e] || [], t && (this.listeners[e] = this.listeners[e].filter(
      (n) => n !== t
    )), (!t || this.listeners[e].length === 0) && (this.events[e] && (this.socket.removeListener(e, this.events[e]), delete this.events[e]), delete this.listeners[e]);
  }
};
var L = class extends V {
  /**
   * Send a whisper event to other clients in the channel.
   */
  whisper(e, t) {
    return this.socket.emit("client event", {
      channel: this.name,
      event: `client-${e}`,
      data: t
    }), this;
  }
};
var K = class extends L {
  /**
   * Register a callback to be called anytime the member list changes.
   */
  here(e) {
    return this.on("presence:subscribed", (t) => {
      e(t.map((n) => n.user_info));
    }), this;
  }
  /**
   * Listen for someone joining the channel.
   */
  joining(e) {
    return this.on(
      "presence:joining",
      (t) => e(t.user_info)
    ), this;
  }
  /**
   * Send a whisper event to other clients in the channel.
   */
  whisper(e, t) {
    return this.socket.emit("client event", {
      channel: this.name,
      event: `client-${e}`,
      data: t
    }), this;
  }
  /**
   * Listen for someone leaving the channel.
   */
  leaving(e) {
    return this.on(
      "presence:leaving",
      (t) => e(t.user_info)
    ), this;
  }
};
var g = class extends k {
  /**
   * Subscribe to a channel.
   */
  subscribe() {
  }
  /**
   * Unsubscribe from a channel.
   */
  unsubscribe() {
  }
  /**
   * Listen for an event on the channel instance.
   */
  listen(e, t) {
    return this;
  }
  /**
   * Listen for all events on the channel instance.
   */
  listenToAll(e) {
    return this;
  }
  /**
   * Stop listening for an event on the channel instance.
   */
  stopListening(e, t) {
    return this;
  }
  /**
   * Register a callback to be called anytime a subscription succeeds.
   */
  subscribed(e) {
    return this;
  }
  /**
   * Register a callback to be called anytime an error occurs.
   */
  error(e) {
    return this;
  }
  /**
   * Bind a channel to an event.
   */
  on(e, t) {
    return this;
  }
};
var O = class extends g {
  /**
   * Send a whisper event to other clients in the channel.
   */
  whisper(e, t) {
    return this;
  }
};
var N = class extends g {
  /**
   * Send a whisper event to other clients in the channel.
   */
  whisper(e, t) {
    return this;
  }
};
var X = class extends O {
  /**
   * Register a callback to be called anytime the member list changes.
   */
  here(e) {
    return this;
  }
  /**
   * Listen for someone joining the channel.
   */
  joining(e) {
    return this;
  }
  /**
   * Send a whisper event to other clients in the channel.
   */
  whisper(e, t) {
    return this;
  }
  /**
   * Listen for someone leaving the channel.
   */
  leaving(e) {
    return this;
  }
};
var j = class $ {
  /**
   * Create a new class instance.
   */
  constructor(e) {
    this.setOptions(e), this.connect();
  }
  /**
   * Merge the custom options with the defaults.
   */
  setOptions(e) {
    this.options = {
      ...$._defaultOptions,
      ...e,
      broadcaster: e.broadcaster
    };
    let t = this.csrfToken();
    t && (this.options.auth.headers["X-CSRF-TOKEN"] = t, this.options.userAuthentication.headers["X-CSRF-TOKEN"] = t), t = this.options.bearerToken, t && (this.options.auth.headers.Authorization = "Bearer " + t, this.options.userAuthentication.headers.Authorization = "Bearer " + t);
  }
  /**
   * Extract the CSRF token from the page.
   */
  csrfToken() {
    var e, t;
    return typeof window < "u" && (e = window.Laravel) != null && e.csrfToken ? window.Laravel.csrfToken : this.options.csrfToken ? this.options.csrfToken : typeof document < "u" && typeof document.querySelector == "function" ? ((t = document.querySelector('meta[name="csrf-token"]')) == null ? void 0 : t.getAttribute("content")) ?? null : null;
  }
};
j._defaultOptions = {
  auth: {
    headers: {}
  },
  authEndpoint: "/broadcasting/auth",
  userAuthentication: {
    endpoint: "/broadcasting/user-auth",
    headers: {}
  },
  csrfToken: null,
  bearerToken: null,
  host: null,
  key: null,
  namespace: "App.Events"
};
var T = j;
var m = class extends T {
  constructor() {
    super(...arguments), this.channels = {};
  }
  /**
   * Create a fresh Pusher connection.
   */
  connect() {
    if (typeof this.options.client < "u")
      this.pusher = this.options.client;
    else if (this.options.Pusher)
      this.pusher = new this.options.Pusher(
        this.options.key,
        this.options
      );
    else if (typeof window < "u" && typeof window.Pusher < "u")
      this.pusher = new window.Pusher(this.options.key, this.options);
    else
      throw new Error(
        "Pusher client not found. Should be globally available or passed via options.client"
      );
  }
  /**
   * Sign in the user via Pusher user authentication (https://pusher.com/docs/channels/using_channels/user-authentication/).
   */
  signin() {
    this.pusher.signin();
  }
  /**
   * Listen for an event on a channel instance.
   */
  listen(e, t, n) {
    return this.channel(e).listen(t, n);
  }
  /**
   * Get a channel instance by name.
   */
  channel(e) {
    return this.channels[e] || (this.channels[e] = new I(
      this.pusher,
      e,
      this.options
    )), this.channels[e];
  }
  /**
   * Get a private channel instance by name.
   */
  privateChannel(e) {
    return this.channels["private-" + e] || (this.channels["private-" + e] = new x(
      this.pusher,
      "private-" + e,
      this.options
    )), this.channels["private-" + e];
  }
  /**
   * Get a private encrypted channel instance by name.
   */
  encryptedPrivateChannel(e) {
    return this.channels["private-encrypted-" + e] || (this.channels["private-encrypted-" + e] = new q(
      this.pusher,
      "private-encrypted-" + e,
      this.options
    )), this.channels["private-encrypted-" + e];
  }
  /**
   * Get a presence channel instance by name.
   */
  presenceChannel(e) {
    return this.channels["presence-" + e] || (this.channels["presence-" + e] = new U(
      this.pusher,
      "presence-" + e,
      this.options
    )), this.channels["presence-" + e];
  }
  /**
   * Leave the given channel, as well as its private and presence variants.
   */
  leave(e) {
    [
      e,
      "private-" + e,
      "private-encrypted-" + e,
      "presence-" + e
    ].forEach((t) => {
      this.leaveChannel(t);
    });
  }
  /**
   * Leave the given channel.
   */
  leaveChannel(e) {
    this.channels[e] && (this.channels[e].unsubscribe(), delete this.channels[e]);
  }
  /**
   * Get the socket ID for the connection.
   */
  socketId() {
    return this.pusher.connection.socket_id;
  }
  /**
   * Disconnect Pusher connection.
   */
  disconnect() {
    this.pusher.disconnect();
  }
};
var Q = class extends T {
  constructor() {
    super(...arguments), this.channels = {};
  }
  /**
   * Create a fresh Socket.io connection.
   */
  connect() {
    let e = this.getSocketIO();
    this.socket = e(
      this.options.host ?? void 0,
      this.options
    ), this.socket.io.on("reconnect", () => {
      Object.values(this.channels).forEach((t) => {
        t.subscribe();
      });
    });
  }
  /**
   * Get socket.io module from global scope or options.
   */
  getSocketIO() {
    if (typeof this.options.client < "u")
      return this.options.client;
    if (typeof window < "u" && typeof window.io < "u")
      return window.io;
    throw new Error(
      "Socket.io client not found. Should be globally available or passed via options.client"
    );
  }
  /**
   * Listen for an event on a channel instance.
   */
  listen(e, t, n) {
    return this.channel(e).listen(t, n);
  }
  /**
   * Get a channel instance by name.
   */
  channel(e) {
    return this.channels[e] || (this.channels[e] = new V(
      this.socket,
      e,
      this.options
    )), this.channels[e];
  }
  /**
   * Get a private channel instance by name.
   */
  privateChannel(e) {
    return this.channels["private-" + e] || (this.channels["private-" + e] = new L(
      this.socket,
      "private-" + e,
      this.options
    )), this.channels["private-" + e];
  }
  /**
   * Get a presence channel instance by name.
   */
  presenceChannel(e) {
    return this.channels["presence-" + e] || (this.channels["presence-" + e] = new K(
      this.socket,
      "presence-" + e,
      this.options
    )), this.channels["presence-" + e];
  }
  /**
   * Leave the given channel, as well as its private and presence variants.
   */
  leave(e) {
    [e, "private-" + e, "presence-" + e].forEach((t) => {
      this.leaveChannel(t);
    });
  }
  /**
   * Leave the given channel.
   */
  leaveChannel(e) {
    this.channels[e] && (this.channels[e].unsubscribe(), delete this.channels[e]);
  }
  /**
   * Get the socket ID for the connection.
   */
  socketId() {
    return this.socket.id;
  }
  /**
   * Disconnect Socketio connection.
   */
  disconnect() {
    this.socket.disconnect();
  }
};
var P = class extends T {
  constructor() {
    super(...arguments), this.channels = {};
  }
  /**
   * Create a fresh connection.
   */
  connect() {
  }
  /**
   * Listen for an event on a channel instance.
   */
  listen(e, t, n) {
    return new g();
  }
  /**
   * Get a channel instance by name.
   */
  channel(e) {
    return new g();
  }
  /**
   * Get a private channel instance by name.
   */
  privateChannel(e) {
    return new O();
  }
  /**
   * Get a private encrypted channel instance by name.
   */
  encryptedPrivateChannel(e) {
    return new N();
  }
  /**
   * Get a presence channel instance by name.
   */
  presenceChannel(e) {
    return new X();
  }
  /**
   * Leave the given channel, as well as its private and presence variants.
   */
  leave(e) {
  }
  /**
   * Leave the given channel.
   */
  leaveChannel(e) {
  }
  /**
   * Get the socket ID for the connection.
   */
  socketId() {
    return "fake-socket-id";
  }
  /**
   * Disconnect the connection.
   */
  disconnect() {
  }
};
var W = class {
  /**
   * Create a new class instance.
   */
  constructor(e) {
    this.options = e, this.connect(), this.options.withoutInterceptors || this.registerInterceptors();
  }
  /**
   * Get a channel instance by name.
   */
  channel(e) {
    return this.connector.channel(e);
  }
  /**
   * Create a new connection.
   */
  connect() {
    if (this.options.broadcaster === "reverb")
      this.connector = new m({
        ...this.options,
        cluster: ""
      });
    else if (this.options.broadcaster === "pusher")
      this.connector = new m(this.options);
    else if (this.options.broadcaster === "ably")
      this.connector = new m({
        ...this.options,
        cluster: "",
        broadcaster: "pusher"
      });
    else if (this.options.broadcaster === "socket.io")
      this.connector = new Q(this.options);
    else if (this.options.broadcaster === "null")
      this.connector = new P(this.options);
    else if (typeof this.options.broadcaster == "function" && B(this.options.broadcaster))
      this.connector = new this.options.broadcaster(this.options);
    else
      throw new Error(
        `Broadcaster ${typeof this.options.broadcaster} ${String(this.options.broadcaster)} is not supported.`
      );
  }
  /**
   * Disconnect from the Echo server.
   */
  disconnect() {
    this.connector.disconnect();
  }
  /**
   * Get a presence channel instance by name.
   */
  join(e) {
    return this.connector.presenceChannel(e);
  }
  /**
   * Leave the given channel, as well as its private and presence variants.
   */
  leave(e) {
    this.connector.leave(e);
  }
  /**
   * Leave the given channel.
   */
  leaveChannel(e) {
    this.connector.leaveChannel(e);
  }
  /**
   * Leave all channels.
   */
  leaveAllChannels() {
    for (const e in this.connector.channels)
      this.leaveChannel(e);
  }
  /**
   * Listen for an event on a channel instance.
   */
  listen(e, t, n) {
    return this.connector.listen(e, t, n);
  }
  /**
   * Get a private channel instance by name.
   */
  private(e) {
    return this.connector.privateChannel(e);
  }
  /**
   * Get a private encrypted channel instance by name.
   */
  encryptedPrivate(e) {
    if (this.connectorSupportsEncryptedPrivateChannels(this.connector))
      return this.connector.encryptedPrivateChannel(e);
    throw new Error(
      `Broadcaster ${typeof this.options.broadcaster} ${String(
        this.options.broadcaster
      )} does not support encrypted private channels.`
    );
  }
  connectorSupportsEncryptedPrivateChannels(e) {
    return e instanceof m || e instanceof P;
  }
  /**
   * Get the Socket ID for the connection.
   */
  socketId() {
    return this.connector.socketId();
  }
  /**
   * Register 3rd party request interceptiors. These are used to automatically
   * send a connections socket id to a Laravel app with a X-Socket-Id header.
   */
  registerInterceptors() {
    typeof Vue < "u" && Vue != null && Vue.http && this.registerVueRequestInterceptor(), typeof axios == "function" && this.registerAxiosRequestInterceptor(), typeof jQuery == "function" && this.registerjQueryAjaxSetup(), typeof Turbo == "object" && this.registerTurboRequestInterceptor();
  }
  /**
   * Register a Vue HTTP interceptor to add the X-Socket-ID header.
   */
  registerVueRequestInterceptor() {
    Vue.http.interceptors.push(
      (e, t) => {
        this.socketId() && e.headers.set("X-Socket-ID", this.socketId()), t();
      }
    );
  }
  /**
   * Register an Axios HTTP interceptor to add the X-Socket-ID header.
   */
  registerAxiosRequestInterceptor() {
    axios.interceptors.request.use(
      (e) => (this.socketId() && (e.headers["X-Socket-Id"] = this.socketId()), e)
    );
  }
  /**
   * Register jQuery AjaxPrefilter to add the X-Socket-ID header.
   */
  registerjQueryAjaxSetup() {
    typeof jQuery.ajax < "u" && jQuery.ajaxPrefilter(
      (e, t, n) => {
        this.socketId() && n.setRequestHeader("X-Socket-Id", this.socketId());
      }
    );
  }
  /**
   * Register the Turbo Request interceptor to add the X-Socket-ID header.
   */
  registerTurboRequestInterceptor() {
    document.addEventListener(
      "turbo:before-fetch-request",
      (e) => {
        e.detail.fetchOptions.headers["X-Socket-Id"] = this.socketId();
      }
    );
  }
};
var p = null;
var u = null;
var Y = () => {
  if (p)
    return p;
  if (!u)
    throw new Error(
      "Echo has not been configured. Please call `configureEcho()` with your configuration options before using Echo."
    );
  return u.Pusher ?? (u.Pusher = import_pusher_js.default), p = new W(u), p;
};
var Z = () => u !== null;
var ee = (s) => {
  u = {
    ...{
      reverb: {
        broadcaster: "reverb",
        key: typeof import.meta.env !== "undefined" ? import.meta.env.VITE_REVERB_APP_KEY : void 0,
        wsHost: typeof import.meta.env !== "undefined" ? import.meta.env.VITE_REVERB_HOST : void 0,
        wsPort: typeof import.meta.env !== "undefined" ? import.meta.env.VITE_REVERB_PORT : void 0,
        wssPort: typeof import.meta.env !== "undefined" ? import.meta.env.VITE_REVERB_PORT : void 0,
        forceTLS: ((typeof import.meta.env !== "undefined" ? import.meta.env.VITE_REVERB_SCHEME : void 0) ?? "https") === "https",
        enabledTransports: ["ws", "wss"]
      },
      pusher: {
        broadcaster: "pusher",
        key: typeof import.meta.env !== "undefined" ? import.meta.env.VITE_PUSHER_APP_KEY : void 0,
        cluster: typeof import.meta.env !== "undefined" ? import.meta.env.VITE_PUSHER_APP_CLUSTER : void 0,
        forceTLS: true,
        wsHost: typeof import.meta.env !== "undefined" ? import.meta.env.VITE_PUSHER_HOST : void 0,
        wsPort: typeof import.meta.env !== "undefined" ? import.meta.env.VITE_PUSHER_PORT : void 0,
        wssPort: typeof import.meta.env !== "undefined" ? import.meta.env.VITE_PUSHER_PORT : void 0,
        enabledTransports: ["ws", "wss"]
      },
      "socket.io": {
        broadcaster: "socket.io",
        host: typeof import.meta.env !== "undefined" ? import.meta.env.VITE_SOCKET_IO_HOST : void 0
      },
      null: {
        broadcaster: "null"
      },
      ably: {
        broadcaster: "pusher",
        key: typeof import.meta.env !== "undefined" ? import.meta.env.VITE_ABLY_PUBLIC_KEY : void 0,
        wsHost: "realtime-pusher.ably.io",
        wsPort: 443,
        disableStats: true,
        encrypted: true
      }
    }[s.broadcaster],
    ...s
  }, p && (p = null);
};
var y = () => Y();
var F = (s) => Array.isArray(s) ? s : [s];
var c = {};
var z = (s) => {
  if (c[s.id])
    return c[s.id].count += 1, c[s.id].connection;
  const e = M(s);
  return c[s.id] = {
    count: 1,
    connection: e
  }, e;
};
var M = (s) => {
  const e = y();
  return s.visibility === "presence" ? e.join(s.name) : s.visibility === "private" ? e.private(s.name) : e.channel(s.name);
};
var D = (s, e = false) => {
  c[s.id] && (c[s.id].count -= 1, !(c[s.id].count > 0) && (delete c[s.id], e ? y().leave(s.name) : y().leaveChannel(s.id)));
};
var E = (s, e = [], t = () => {
}, n = [], i = "private") => {
  const r = ref(t), h = ref(false);
  watch(
    () => t,
    (a) => {
      r.value = a;
    }
  );
  const d = {
    name: s,
    id: ["private", "presence"].includes(i) ? `${i}-${s}` : s,
    visibility: i
  }, l = z(d), b = Array.isArray(e) ? e : [e], v = () => {
    o();
  }, o = () => {
    h.value || (b.forEach((a) => {
      l.listen(a, r.value);
    }), h.value = true);
  }, _ = () => {
    h.value && (b.forEach((a) => {
      l.stopListening(a, r.value);
    }), h.value = false);
  }, f = (a = false) => {
    _(), D(d, a);
  };
  return onMounted(() => {
    v();
  }), onUnmounted(() => {
    f();
  }), n.length > 0 && watch(
    // eslint-disable-next-line @typescript-eslint/no-unsafe-return
    () => n,
    () => {
      f(), v();
    },
    { deep: true }
  ), {
    /**
     * Leave the channel
     */
    leaveChannel: f,
    /**
     * Leave the channel and also its associated private and presence channels
     */
    leave: () => f(true),
    /**
     * Stop listening for event(s) without leaving the channel
     */
    stopListening: _,
    /**
     * Listen for event(s)
     */
    listen: o,
    /**
     * Channel instance
     */
    channel: () => l
  };
};
var te = (s, e = () => {
}, t = [], n = []) => {
  const i = E(
    s,
    [],
    e,
    n,
    "private"
  ), r = F(t).map((o) => o.includes(".") ? [o, o.replace(/\./g, "\\")] : [o, o.replace(/\\/g, ".")]).flat(), h = ref(false), d = ref(false), l = (o) => {
    h.value && (r.length === 0 || r.includes(o.type)) && e(o);
  }, b = () => {
    h.value || (d.value || i.channel().notification(l), h.value = true, d.value = true);
  }, v = () => {
    h.value && (i.channel().stopListeningForNotification(l), h.value = false);
  };
  return onMounted(() => {
    b();
  }), onUnmounted(() => {
    v();
  }), {
    ...i,
    /**
     * Stop listening for notification events
     */
    stopListening: v,
    /**
     * Listen for notification events
     */
    listen: b
  };
};
var se = (s, e = [], t = () => {
}, n = []) => E(
  s,
  e,
  t,
  n,
  "presence"
);
var ne = (s, e = [], t = () => {
}, n = []) => E(
  s,
  e,
  t,
  n,
  "public"
);
var ie = (s, e, t = [], n = () => {
}, i = []) => E(
  `${s}.${e}`,
  F(t).map((r) => r.startsWith(".") ? r : `.${r}`),
  n,
  i,
  "private"
);
export {
  ee as configureEcho,
  y as echo,
  Z as echoIsConfigured,
  E as useEcho,
  ie as useEchoModel,
  te as useEchoNotification,
  se as useEchoPresence,
  ne as useEchoPublic
};
//# sourceMappingURL=@laravel_echo-vue.js.map
