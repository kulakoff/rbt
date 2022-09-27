import SyslogServer, { SyslogError } from "ts-syslog";
const server = new SyslogServer();

server.on("message", (value) => {
  console.log(value.date); // the date/time the message was received
  console.log(value.message); // the syslog message
});

server.on("error", (err: SyslogError) => {
  console.error(err.message);
});

server.listen({ port: 514 }, () => {
  console.log("Syslog listening on port 514");
});
