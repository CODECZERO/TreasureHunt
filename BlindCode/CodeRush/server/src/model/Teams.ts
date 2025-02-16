import mongoose from "mongoose";


const TeamsSchema=new mongoose.Schema({
    TeamName:{
        type:String,
        unique:true,
        required: true,
    },
    level:{
        type:[String],
        required: true,
        index:true,
    },
    Scode:{
        type:[String],
        required: true,
        unique:true,
        index:true,
    }
},{timestamps:true});

export const Teams=mongoose.model("teams",TeamsSchema);