import amqplib, { Channel, Connection } from 'amqplib';

class rabbitMqFunction {
    private exchangeName = 'TechHubWork';
    private messageTTL = 2 * 24 * 60 * 60 * 1000; // 2 days in milliseconds
    public channel!: Channel;
    public queue: any;
    public connection!: Connection;

    // private queueName = "MessageStore"
    public connectRabbitMq = async (roomName: string) => {
        if (!this.connection) this.connection = await amqplib.connect(process.env.RABBITMQURL as string);
        if (!this.channel) {
            this.channel = await this.connection.createChannel();

            this.channel.assertExchange(this.exchangeName, 'topic', {
                durable: false,
            });
        }
    };

    public publishData = async (messageEnc: string, roomName: string) => {
        if (!this.channel) {
            await this.connectRabbitMq(roomName);
        }
        await this.channel.publish(
            this.exchangeName,
            roomName,
            Buffer.from(messageEnc),
            { persistent: false },
        );
    };


    public subData = async (roomName: string) => {
        if (!this.channel) {
            await this.connectRabbitMq(roomName);
        }
        this.queue = await this.channel.assertQueue(roomName, { exclusive: true,autoDelete:true,durable:false, arguments: { 'x-message-ttl': this.messageTTL, } });
        const message = await this.channel.bindQueue(
            this.queue.queue,
            this.exchangeName,
            roomName,
        );
        return message;
    };

    public closeConnection = async () => {
        await this.channel.close();
        await this.connection.close();
    };
}

const rabbitmq = new rabbitMqFunction();
export default rabbitmq;