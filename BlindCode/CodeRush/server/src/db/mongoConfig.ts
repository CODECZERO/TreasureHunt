import mongoose from 'mongoose';

const connectDb = async () => {//connects data base and handle error
    try {
        const connectionInstance = await mongoose.connect(`${process.env.MONGODB_URL}`);
        return connectionInstance;
    } catch (error) {
        console.log(`There is error while connecting to db \n \t ${error} `)
        process.exit(1);
    }
}



const closeDb = async (client:any) => {//this function close mongodb database connection
    try {
        await client.close();
        console.log("Disconnected from MongoDB");
        process.exit(0);
    } catch (error) {
        console.log(`something went wrong while closeing mongodb connection ${error}`);
    }
}


export { connectDb, closeDb };