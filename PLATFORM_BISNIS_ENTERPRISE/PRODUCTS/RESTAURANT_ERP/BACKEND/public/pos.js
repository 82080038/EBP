function createOrder(){


let order={


customer_id:null,


items:[


{
menu_id:10,
qty:2,
price:25000
},


{
menu_id:20,
qty:1,
price:10000
}


]


};



$.ajax({


url:"/api/v1/orders",


method:"POST",


contentType:"application/json",


data:
JSON.stringify(order),



success:function(response){



if(response.success){


alert(
"Order nomor "
+
response.data.order_id
);


}



}



});


}
