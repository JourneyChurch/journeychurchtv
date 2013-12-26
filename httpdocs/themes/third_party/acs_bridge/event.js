(function() {
	var template = Handlebars.template, templates = Handlebars.templates = Handlebars.templates || {};
	templates['event'] = template(function (Handlebars,depth0,helpers,partials,data) {
		this.compilerInfo = [4,'>= 1.0.0'];
		helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
		var buffer = "", stack1, stack2, stack3, options, functionType="function", escapeExpression=this.escapeExpression, self=this, helperMissing=helpers.helperMissing;
		
		function program1(depth0,data) {
  
  
			return "\n				<h2>Details</h2>\n				";
		}

		function program3(depth0,data) {
  
			var buffer = "", stack1;
			buffer += "\n				<img src=\"";
			if (stack1 = helpers.Image) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
			else { stack1 = depth0.Image; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
			buffer += escapeExpression(stack1)
			+ "\" alt=\"";
			if (stack1 = helpers.EventName) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
			else { stack1 = depth0.EventName; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
			buffer += escapeExpression(stack1)
			+ "\" align=\"left\">\n				";
			return buffer;
		}

		function program5(depth0,data) {
  
			var buffer = "", stack1;
			buffer += "<br>	\n				<h2>Contact Information</h2>\n				";
			stack1 = helpers.each.call(depth0, depth0.Sections, {hash:{},inverse:self.noop,fn:self.program(6, program6, data),data:data});
			if(stack1 || stack1 === 0) { buffer += stack1; }
			buffer += "\n				<br>\n				";
			return buffer;
		}
		function program6(depth0,data) {
  
			var buffer = "", stack1;
			buffer += "\n				<strong>Email:</strong> ";
			if (stack1 = helpers.Contactemail) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
			else { stack1 = depth0.Contactemail; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
			buffer += escapeExpression(stack1)
			+ "<br />\n				<strong>Phone:</strong> ";
			if (stack1 = helpers.ContactPhone) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
			else { stack1 = depth0.ContactPhone; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
			buffer += escapeExpression(stack1)
			+ "<br />\n				";
			return buffer;
		}

		function program8(depth0,data) {
  
			var buffer = "", stack1;
			buffer += "\n				<h2>Registration Periods</h2>\n				";
			stack1 = helpers.each.call(depth0, depth0.RegistrationPeriods, {hash:{},inverse:self.noop,fn:self.program(9, program9, data),data:data});
			if(stack1 || stack1 === 0) { buffer += stack1; }
			buffer += "\n						\n				<a href=\"https://secure.accessacs.com/access/login_guest.aspx?sn=106649\"><button class=\"pull-right\">Register Now</button></a>\n				";
			return buffer;
		}
		function program9(depth0,data) {
  
			var buffer = "", stack1, options;
			buffer += "\n				";
			if (stack1 = helpers.Name) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
			else { stack1 = depth0.Name; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
			buffer += escapeExpression(stack1)
			+ "   $";
			if (stack1 = helpers.Cost) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
			else { stack1 = depth0.Cost; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
			buffer += escapeExpression(stack1)
			+ "   From ";
			options = {hash:{},data:data};
			buffer += escapeExpression(((stack1 = helpers.dateFormat || depth0.dateFormat),stack1 ? stack1.call(depth0, depth0.StartTime, options) : helperMissing.call(depth0, "dateFormat", depth0.StartTime, options)))
			+ " to ";
			options = {hash:{},data:data};
			buffer += escapeExpression(((stack1 = helpers.dateFormat || depth0.dateFormat),stack1 ? stack1.call(depth0, depth0.EndTime, options) : helperMissing.call(depth0, "dateFormat", depth0.EndTime, options)))
			+ "\n				";
			return buffer;
		}
		// Set title window
		if (stack1 = helpers.EventName) { window.parent.document.title = helpers.EventName + " - JourneyChurch.tv" }
		else { window.parent.document.title = depth0.EventName + " - JourneyChurch.tv" }
		
		// Create the top title bar
		buffer += "<div class=\"title-bar\">\n	<div class=\"container\">\n		<div class=\"grid_12\">\n			<h2>";
		if (stack1 = helpers.EventName) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
		else { stack1 = depth0.EventName; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
		buffer += escapeExpression(stack1)
		+			"</h2>"
		+		"</div>"
		+		"<div class='clear'></div>"
		+	"</div>"
		+"</div>"
		// Start the container and create Summary h2
		+"<div class='container clearfix'>"
		+	"<div class='grid_12'>"
		+		"<div class='entry fancy-page'>"
		+			"<h2>Summary</h2>"
					// Set the day / time
		+			"<p><i class='fa fa-clock-o med-icon'></i> ";
						options = {hash:{},data:data};
						buffer += escapeExpression(((stack1 = helpers.dateFormat || depth0.dateFormat),stack1 ? stack1.call(depth0, depth0.StartDate, options) : helperMissing.call(depth0, "dateFormat", depth0.StartDate, options)))
		+ 			"</p>"
					// Set the location
		+ 			"<p> ";
					if (stack2 = helpers.Location) { stack2 = stack2.call(depth0, {hash:{},data:data});}
					else { stack2 = depth0.Location; stack2 = typeof stack2 === functionType ? stack2.apply(depth0) : stack2; }
					buffer += escapeExpression(stack2)
		+ 			"</p>"
					// Show the details
		+			"<p class='body'>";
						stack2 = helpers['if'].call(depth0, depth0.Description, {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
						if(stack2 || stack2 === 0) { buffer += stack2; }
						buffer += "\n						\n				";
						stack2 = helpers['if'].call(depth0, depth0.Image, {hash:{},inverse:self.noop,fn:self.program(3, program3, data),data:data});
						if(stack2 || stack2 === 0) { buffer += stack2; }
						buffer += "\n				";
						if (stack2 = helpers.Description) { stack2 = stack2.call(depth0, {hash:{},data:data}); }
						else { stack2 = depth0.Description; stack2 = typeof stack2 === functionType ? stack2.apply(depth0) : stack2; }
						if(stack2 || stack2 === 0) { buffer += stack2; }
						buffer += "<div class='clear'></div>";
						stack2 = helpers['if'].call(depth0, depth0.Sections, {hash:{},inverse:self.noop,fn:self.program(5, program5, data),data:data});
						if(stack2 || stack2 === 0) { buffer += stack2; }
						buffer += "\n\n				";
						stack2 = helpers['if'].call(depth0, depth0.RegistrationPeriods, {hash:{},inverse:self.noop,fn:self.program(8, program8, data),data:data});
						if(stack2 || stack2 === 0) { buffer += stack2; }
					buffer += escapeExpression(stack3)
		+			"</p>"
		+		"</div>"
		+	"</div>"
		+"</div>";
		return buffer;
	});
})();